<?php

namespace App\Services;

use App\Models\Location;
use App\Models\RackConfirmation;
use App\Models\RackConfirmationHistory;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RackConfirmationService
{
    public function __construct(
        private ActiveStoService $activeStoService,
        private ActivityLogService $activityLog,
    ) {}

    public function transition(User $actor, Location $location, string $action, ?string $note = null): RackConfirmation
    {
        $activeSto = $this->activeStoService->active();

        if (! $activeSto) {
            throw ValidationException::withMessages(['sto' => ActiveStoService::NO_ACTIVE_STO_MESSAGE]);
        }

        $this->authorize($actor, $action);

        $confirmation = DB::transaction(function () use ($actor, $location, $activeSto, $action, $note) {
            $confirmation = RackConfirmation::query()->firstOrCreate(
                ['sto_code_id' => $activeSto->id, 'location_id' => $location->id],
                ['status' => RackConfirmation::STATUS_PENDING],
            );
            $confirmation = RackConfirmation::query()->lockForUpdate()->findOrFail($confirmation->id);

            $fromStatus = $confirmation->status;
            $toStatus = $this->targetStatus($action);
            $this->assertTransition($action, $fromStatus);

            $attributes = ['status' => $toStatus, 'note' => $note];
            if ($action === 'finish') {
                $attributes += ['finished_by_user_id' => $actor->id, 'finished_at' => now()];
            } elseif ($action === 'validate') {
                $attributes += ['validated_by_user_id' => $actor->id, 'validated_at' => now()];
            } elseif ($action === 'confirm') {
                $attributes += ['confirmed_by_user_id' => $actor->id, 'confirmed_at' => now()];
            } else {
                $attributes += [
                    'finished_by_user_id' => null,
                    'finished_at' => null,
                    'validated_by_user_id' => null,
                    'validated_at' => null,
                    'confirmed_by_user_id' => null,
                    'confirmed_at' => null,
                ];
            }

            $confirmation->update($attributes);

            if ($action === 'confirm') {
                $location->update([
                    'is_confirmed' => true,
                    'confirmed_by_user_id' => $actor->id,
                    'confirmed_at' => now(),
                    'confirmation_note' => $note,
                ]);
            } elseif ($action === 'cancel') {
                $location->update([
                    'is_confirmed' => false,
                    'confirmed_by_user_id' => null,
                    'confirmed_at' => null,
                    'confirmation_note' => null,
                ]);
            }

            RackConfirmationHistory::create([
                'rack_confirmation_id' => $confirmation->id,
                'sto_code_id' => $activeSto->id,
                'location_id' => $location->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'action_by_user_id' => $actor->id,
                'note' => $note,
                'metadata' => ['action' => $action],
            ]);

            $this->activityLog->recordOrFail(
                $actor,
                'rack_confirmation.'.$action,
                $confirmation,
                ['status' => $fromStatus],
                ['status' => $toStatus, 'note' => $note],
            );

            return $confirmation->fresh();
        });

        Cache::forget('sidebar_badges');

        return $confirmation;
    }

    public function allowedActions(User $user, string $status): array
    {
        return [
            'finish' => $user->isScanner() && $status === RackConfirmation::STATUS_PENDING,
            'validate' => $user->isScanner() && $user->is_validator && $status === RackConfirmation::STATUS_FINISHED,
            'confirm' => $user->isAdmin() && $status === RackConfirmation::STATUS_VALIDATED,
            'cancel' => $user->isAdmin() && $status !== RackConfirmation::STATUS_PENDING,
        ];
    }

    private function authorize(User $actor, string $action): void
    {
        $allowed = match ($action) {
            'finish' => $actor->isScanner(),
            'validate' => $actor->isScanner() && $actor->is_validator,
            'confirm', 'cancel' => $actor->isAdmin(),
            default => false,
        };

        if (! $allowed) {
            throw new AuthorizationException('Anda tidak memiliki akses untuk aksi Rack Confirmation ini.');
        }
    }

    private function targetStatus(string $action): string
    {
        return match ($action) {
            'finish' => RackConfirmation::STATUS_FINISHED,
            'validate' => RackConfirmation::STATUS_VALIDATED,
            'confirm' => RackConfirmation::STATUS_CONFIRMED,
            'cancel' => RackConfirmation::STATUS_PENDING,
            default => throw ValidationException::withMessages(['action' => 'Aksi Rack Confirmation tidak valid.']),
        };
    }

    private function assertTransition(string $action, string $fromStatus): void
    {
        $expected = match ($action) {
            'finish' => RackConfirmation::STATUS_PENDING,
            'validate' => RackConfirmation::STATUS_FINISHED,
            'confirm' => RackConfirmation::STATUS_VALIDATED,
            default => null,
        };

        if (($action === 'cancel' && $fromStatus === RackConfirmation::STATUS_PENDING)
            || ($expected !== null && $fromStatus !== $expected)) {
            throw ValidationException::withMessages([
                'status' => "Transisi {$action} tidak dapat dilakukan dari status {$fromStatus}.",
            ]);
        }
    }
}
