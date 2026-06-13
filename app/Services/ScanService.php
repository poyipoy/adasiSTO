<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ScanResult;
use App\Models\ScanResultLog;
use App\Models\StoCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ScanService
{
    public function __construct(
        private BarcodeParserService $barcodeParser,
        private STOService $stoService,
    ) {}

    public function preview(string $qr): array
    {
        return $this->barcodeParser->parse($qr);
    }

    public function isDuplicate(string $barcodeMaterial, ?string $stoCode = null): bool
    {
        $activeSto = $stoCode ? null : $this->stoService->active();
        $resolvedStoCode = $stoCode ?: $activeSto?->code;

        if (!$resolvedStoCode) {
            return false;
        }

        return ScanResult::query()
            ->where('sto_code', $resolvedStoCode)
            ->where('barcode_material', strtoupper(trim($barcodeMaterial)))
            ->exists();
    }

    public function store(User $user, array $payload): array
    {
        $activeSto = $this->stoService->active();

        if (!$activeSto) {
            return [
                'success' => false,
                'status' => 422,
                'message' => STOService::NO_ACTIVE_STO_MESSAGE,
            ];
        }

        $parsed = $this->barcodeParser->parse($payload['qr']);

        if (!$parsed['valid']) {
            return [
                'success' => false,
                'status' => 422,
                'message' => $parsed['message'],
            ];
        }

        if ($this->isDuplicate($parsed['barcode_material'], $activeSto->code) && empty($payload['force_save'])) {
            return [
                'success' => false,
                'status' => 409,
                'duplicate' => true,
                'message' => 'Barcode sudah pernah discan sebelumnya. Tetap simpan?',
            ];
        }

        $scanResult = DB::transaction(function () use ($user, $payload, $parsed, $activeSto) {
            $scanResult = ScanResult::create([
                'user_id' => $user->id,
                'sto_code_id' => $activeSto->id,
                'plant_id' => $payload['plant_id'],
                'location_id' => $payload['location_id'],
                'sto_code' => $activeSto->code,
                'barcode_raw' => $payload['qr'],
                'barcode_material' => $parsed['barcode_material'],
                'lot_number' => $parsed['lot_number'],
                'qty' => $parsed['qty'],
                'material_code' => $parsed['material_code'],
                'material_name' => $parsed['material_name'],
                'shape_code' => $parsed['shape_code'],
                'shape_name' => $parsed['shape_name'],
                'thickness' => $parsed['thickness'],
                'width' => $parsed['width'],
                'diameter' => $parsed['diameter'],
                'length' => $parsed['length'],
                'keterangan' => 'OK',
                'scan_source' => $payload['scan_source'] ?? 'manual',
            ]);

            $this->logSnapshot($scanResult, $user->id, 'created', newValue: $scanResult->toArray());

            return $scanResult;
        });

        return [
            'success' => true,
            'message' => 'Scan berhasil disimpan.',
            'data' => $this->serializeScan($scanResult->load(['user', 'plant', 'location'])),
        ];
    }

    public function historyQuery(User $user, array $filters = []): Builder
    {
        $query = ScanResult::query()
            ->with(['plant', 'location'])
            ->forUser($user->id)
            ->latestFirst();

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['barcode_material'])) {
            $query->where('barcode_material', strtoupper(trim($filters['barcode_material'])));
        }

        if (!empty($filters['material_code'])) {
            $query->where('material_code', strtoupper(trim($filters['material_code'])));
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', (int) $filters['location_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function historyFilterOptions(User $user): array
    {
        return [
            'barcodes' => ScanResult::query()
                ->forUser($user->id)
                ->select('barcode_material')
                ->selectRaw('MAX(created_at) as latest_created_at')
                ->selectRaw('MAX(id) as latest_id')
                ->groupBy('barcode_material')
                ->orderByDesc('latest_created_at')
                ->orderByDesc('latest_id')
                ->get()
                ->map(fn (ScanResult $scanResult) => [
                    'value' => $scanResult->barcode_material,
                    'label' => $scanResult->barcode_material,
                ])
                ->values(),
            'materials' => ScanResult::query()
                ->forUser($user->id)
                ->select('material_code', 'material_name')
                ->selectRaw('MAX(created_at) as latest_created_at')
                ->selectRaw('MAX(id) as latest_id')
                ->groupBy('material_code', 'material_name')
                ->orderByDesc('latest_created_at')
                ->orderByDesc('latest_id')
                ->get()
                ->map(fn (ScanResult $scanResult) => [
                    'value' => $scanResult->material_code,
                    'label' => "{$scanResult->material_name} ({$scanResult->material_code})",
                ])
                ->values(),
            'locations' => ScanResult::query()
                ->join('locations', 'locations.id', '=', 'scan_results.location_id')
                ->where('scan_results.user_id', $user->id)
                ->select('locations.id', 'locations.name')
                ->selectRaw('MAX(scan_results.created_at) as latest_created_at')
                ->selectRaw('MAX(scan_results.id) as latest_id')
                ->groupBy('locations.id', 'locations.name')
                ->orderByDesc('latest_created_at')
                ->orderByDesc('latest_id')
                ->get()
                ->map(fn ($location) => [
                    'value' => $location->id,
                    'label' => $location->name,
                ])
                ->values(),
        ];
    }

    public function deleteForScanner(User $user, int $id): void
    {
        $scanResult = ScanResult::findOrFail($id);

        if ($scanResult->user_id !== $user->id) {
            throw new AuthorizationException('Anda tidak memiliki akses untuk menghapus data ini.');
        }

        $this->deleteWithAudit($scanResult, $user->id);
    }

    public function updateByAdmin(User $admin, int $id, array $payload): ScanResult
    {
        return DB::transaction(function () use ($admin, $id, $payload) {
            $scanResult = ScanResult::findOrFail($id);
            $oldValues = $this->auditValues($scanResult);
            $stoCode = StoCode::findOrFail($payload['sto_code_id']);
            $location = $this->resolveLocation($payload['user_id'], $payload['plant_id'], $payload['location_name']);

            $scanResult->forceFill($this->manualScanAttributes($payload, $stoCode, $location));
            $scanResult->created_at = Carbon::parse($payload['created_at']);
            $scanResult->save();
            $scanResult->refresh();

            $newValues = $this->auditValues($scanResult);
            foreach ($newValues as $field => $newValue) {
                $oldValue = $oldValues[$field] ?? null;
                if ((string) $oldValue !== (string) $newValue) {
                    $this->logFieldChange($scanResult, $admin->id, $field, $oldValue, $newValue);
                }
            }

            return $scanResult->refresh();
        });
    }

    public function storeByAdmin(User $admin, array $payload): array
    {
        $stoCode = StoCode::findOrFail($payload['sto_code_id']);
        $barcodeMaterial = strtoupper(trim($payload['barcode_material']));

        if ($this->isDuplicate($barcodeMaterial, $stoCode->code) && empty($payload['force_save'])) {
            return [
                'success' => false,
                'status' => 409,
                'duplicate' => true,
                'message' => 'Barcode sudah pernah discan sebelumnya. Tetap simpan?',
            ];
        }

        $scanResult = DB::transaction(function () use ($admin, $payload, $stoCode) {
            $location = $this->resolveLocation($payload['user_id'], $payload['plant_id'], $payload['location_name']);
            $scanResult = new ScanResult($this->manualScanAttributes($payload, $stoCode, $location));
            $scanResult->created_at = Carbon::parse($payload['created_at']);
            $scanResult->updated_at = now();
            $scanResult->save();

            $this->logSnapshot($scanResult, $admin->id, 'created', newValue: $scanResult->toArray());

            return $scanResult;
        });

        return [
            'success' => true,
            'message' => 'Data scan berhasil ditambahkan.',
            'data' => $this->serializeScan($scanResult->load(['user', 'plant', 'location'])),
        ];
    }

    public function deleteByAdmin(User $admin, int $id): void
    {
        $this->deleteWithAudit(ScanResult::findOrFail($id), $admin->id);
    }

    public function serializeScan(ScanResult $scanResult): array
    {
        return [
            'id' => $scanResult->id,
            'user_id' => $scanResult->user_id,
            'sto_code_id' => $scanResult->sto_code_id,
            'plant_id' => $scanResult->plant_id,
            'location_id' => $scanResult->location_id,
            'barcode_raw' => $scanResult->barcode_raw,
            'barcode_material' => $scanResult->barcode_material,
            'material_code' => $scanResult->material_code,
            'material_name' => $scanResult->material_name,
            'shape_code' => $scanResult->shape_code,
            'shape_name' => $scanResult->shape_name,
            'thickness' => $scanResult->thickness,
            'width' => $scanResult->width,
            'diameter' => $scanResult->diameter,
            'length' => $scanResult->length,
            'lot_number' => $scanResult->lot_number,
            'qty' => $scanResult->qty,
            'sto_code' => $scanResult->sto_code,
            'keterangan' => $scanResult->keterangan,
            'scan_source' => $scanResult->scan_source,
            'plant' => $scanResult->plant?->name,
            'location' => $scanResult->location?->name,
            'created_at' => $scanResult->created_at?->format('Y-m-d H:i:s'),
            'time' => $scanResult->created_at?->format('H:i:s'),
            'size' => $scanResult->size,
            'display_size' => $scanResult->size,
            'recent_detail' => $scanResult->recent_detail,
        ];
    }

    private function deleteWithAudit(ScanResult $scanResult, int $userId): void
    {
        DB::transaction(function () use ($scanResult, $userId) {
            $this->logSnapshot($scanResult, $userId, 'deleted', oldValue: $scanResult->toArray());
            $scanResult->delete();
        });
    }

    private function resolveLocation(int $userId, int $plantId, string $locationName): Location
    {
        return Location::updateOrCreate(
            [
                'user_id' => $userId,
                'plant_id' => $plantId,
                'name' => trim($locationName),
            ],
            ['is_active' => true]
        );
    }

    private function manualScanAttributes(array $payload, StoCode $stoCode, Location $location): array
    {
        $shapeCode = $payload['shape_code'];

        return [
            'user_id' => $payload['user_id'],
            'sto_code_id' => $stoCode->id,
            'plant_id' => $payload['plant_id'],
            'location_id' => $location->id,
            'sto_code' => $stoCode->code,
            'barcode_raw' => $payload['barcode_raw'],
            'barcode_material' => strtoupper($payload['barcode_material']),
            'lot_number' => $payload['lot_number'],
            'qty' => $payload['qty'],
            'material_code' => strtoupper($payload['material_code']),
            'material_name' => $payload['material_name'],
            'shape_code' => $shapeCode,
            'shape_name' => $payload['shape_name'],
            'thickness' => $shapeCode === 'RF' ? $payload['thickness'] : null,
            'width' => $shapeCode === 'RF' ? $payload['width'] : null,
            'diameter' => $shapeCode === 'RR' ? $payload['diameter'] : null,
            'length' => $payload['length'],
            'keterangan' => $payload['keterangan'],
            'scan_source' => $payload['scan_source'] ?? 'admin',
        ];
    }

    private function auditValues(ScanResult $scanResult): array
    {
        return [
            'user_id' => $scanResult->user_id,
            'sto_code_id' => $scanResult->sto_code_id,
            'plant_id' => $scanResult->plant_id,
            'location_id' => $scanResult->location_id,
            'sto_code' => $scanResult->sto_code,
            'barcode_raw' => $scanResult->barcode_raw,
            'barcode_material' => $scanResult->barcode_material,
            'lot_number' => $scanResult->lot_number,
            'qty' => $scanResult->qty,
            'material_code' => $scanResult->material_code,
            'material_name' => $scanResult->material_name,
            'shape_code' => $scanResult->shape_code,
            'shape_name' => $scanResult->shape_name,
            'thickness' => $scanResult->thickness,
            'width' => $scanResult->width,
            'diameter' => $scanResult->diameter,
            'length' => $scanResult->length,
            'keterangan' => $scanResult->keterangan,
            'scan_source' => $scanResult->scan_source,
            'created_at' => $scanResult->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function logFieldChange(ScanResult $scanResult, int $userId, string $field, mixed $oldValue, mixed $newValue): void
    {
        ScanResultLog::create([
            'scan_result_id' => $scanResult->id,
            'user_id' => $userId,
            'action' => 'updated',
            'field_name' => $field,
            'old_value' => is_scalar($oldValue) || $oldValue === null ? $oldValue : json_encode($oldValue),
            'new_value' => is_scalar($newValue) || $newValue === null ? $newValue : json_encode($newValue),
        ]);
    }

    private function logSnapshot(
        ScanResult $scanResult,
        int $userId,
        string $action,
        ?array $oldValue = null,
        ?array $newValue = null,
    ): void {
        ScanResultLog::create([
            'scan_result_id' => $scanResult->id,
            'user_id' => $userId,
            'action' => $action,
            'field_name' => null,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
        ]);
    }
}
