<?php

namespace App\Services;

use App\Exceptions\LocationDeletionBlockedException;
use App\Models\BarcodeRequest;
use App\Models\Location;
use App\Models\RackConfirmation;
use App\Models\RackConfirmationHistory;
use App\Models\ScanResult;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LocationService
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function create(User $actor, array $attributes, string $action = 'master.location.created'): Location
    {
        return DB::transaction(function () use ($actor, $attributes, $action) {
            $location = Location::create($this->attributes($attributes, $actor));

            $this->activityLog->recordOrFail(
                $actor,
                $action,
                $location,
                newValues: $location->toArray(),
            );

            return $location;
        });
    }

    public function resolveForScan(User $actor, int $plantId, string $name): Location
    {
        return DB::transaction(function () use ($actor, $plantId, $name) {
            $normalizedName = trim($name);
            $location = Location::query()
                ->where('plant_id', $plantId)
                ->where('name', $normalizedName)
                ->lockForUpdate()
                ->first();

            if (! $location) {
                return $this->create($actor, [
                    'plant_id' => $plantId,
                    'name' => $normalizedName,
                    'is_active' => true,
                ], 'location.created_from_scan');
            }

            if (! $location->is_active) {
                $oldValues = $location->toArray();
                $location->update(['is_active' => true]);
                $this->activityLog->recordOrFail(
                    $actor,
                    'location.reactivated_from_scan',
                    $location,
                    oldValues: $oldValues,
                    newValues: $location->fresh()->toArray(),
                );
            }

            return $location;
        });
    }

    public function delete(User $actor, Location $location, string $action): void
    {
        DB::transaction(function () use ($actor, $location, $action) {
            $location = Location::query()->lockForUpdate()->findOrFail($location->id);
            $this->assertCanDelete($location);

            // A pending row without history is only a workflow placeholder.
            RackConfirmation::query()
                ->where('location_id', $location->id)
                ->where('status', RackConfirmation::STATUS_PENDING)
                ->whereNull('finished_by_user_id')
                ->whereNull('finished_at')
                ->whereNull('validated_by_user_id')
                ->whereNull('validated_at')
                ->whereNull('confirmed_by_user_id')
                ->whereNull('confirmed_at')
                ->whereNull('note')
                ->whereDoesntHave('histories')
                ->delete();

            $oldValues = $location->toArray();

            $this->activityLog->recordOrFail($actor, $action, $location, oldValues: $oldValues);
            $location->delete();
        });
    }

    private function assertCanDelete(Location $location): void
    {
        if (ScanResult::query()
            ->withoutGlobalScope('active_sto')
            ->where('location_id', $location->id)
            ->exists()) {
            throw new LocationDeletionBlockedException(
                'Lokasi tidak dapat dihapus karena sudah digunakan untuk scan. Nonaktifkan lokasi jika tidak lagi digunakan.'
            );
        }

        if (BarcodeRequest::query()->where('location_id', $location->id)->exists()) {
            throw new LocationDeletionBlockedException(
                'Lokasi tidak dapat dihapus karena sudah digunakan pada Request Barcode. Nonaktifkan lokasi jika tidak lagi digunakan.'
            );
        }

        $hasRackHistory = RackConfirmationHistory::query()
            ->where('location_id', $location->id)
            ->exists();
        $hasProcessedConfirmation = RackConfirmation::query()
            ->where('location_id', $location->id)
            ->where(function ($query) {
                $query->where('status', '!=', RackConfirmation::STATUS_PENDING)
                    ->orWhereNotNull('finished_by_user_id')
                    ->orWhereNotNull('finished_at')
                    ->orWhereNotNull('validated_by_user_id')
                    ->orWhereNotNull('validated_at')
                    ->orWhereNotNull('confirmed_by_user_id')
                    ->orWhereNotNull('confirmed_at')
                    ->orWhereNotNull('note');
            })
            ->exists();

        if ($hasRackHistory || $hasProcessedConfirmation) {
            throw new LocationDeletionBlockedException(
                'Lokasi tidak dapat dihapus karena memiliki riwayat Konfirmasi Rak. Nonaktifkan lokasi untuk menjaga histori audit.'
            );
        }
    }

    private function attributes(array $attributes, User $actor): array
    {
        return [
            'plant_id' => (int) $attributes['plant_id'],
            'name' => trim((string) $attributes['name']),
            'old_location_name' => $this->nullableString($attributes['old_location_name'] ?? null),
            'description' => $this->nullableString($attributes['description'] ?? null),
            'warehouse' => $this->nullableString($attributes['warehouse'] ?? null),
            'is_active' => array_key_exists('is_active', $attributes) ? (bool) $attributes['is_active'] : true,
            'created_by_user_id' => $actor->id,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
