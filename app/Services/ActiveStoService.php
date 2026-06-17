<?php

namespace App\Services;

use App\Models\StoCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActiveStoService
{
    public const NO_ACTIVE_STO_MESSAGE = 'Tidak ada STO aktif yang tersedia. Silakan hubungi Admin.';

    public function __construct(private ActivityLogService $activityLog) {}

    public function active(): ?StoCode
    {
        return StoCode::query()
            ->where('is_active', true)
            ->orderByDesc('updated_at')
            ->first();
    }

    public function requireActive(): StoCode
    {
        $activeSto = $this->active();

        if (!$activeSto) {
            abort(422, self::NO_ACTIVE_STO_MESSAGE);
        }

        return $activeSto;
    }

    public function activate(StoCode $stoCode, ?User $actor = null): StoCode
    {
        try {
            return DB::transaction(function () use ($stoCode, $actor) {
                $previousActive = StoCode::query()
                    ->where('is_active', true)
                    ->pluck('code', 'id')
                    ->all();

                StoCode::query()->update(['is_active' => false]);
                $stoCode->forceFill(['is_active' => true])->save();
                $stoCode->refresh();

                $this->activityLog->record(
                    user: $actor,
                    action: 'sto.activated',
                    subject: $stoCode,
                    oldValues: ['previous_active' => $previousActive],
                    newValues: [
                        'sto_code_id' => $stoCode->id,
                        'code' => $stoCode->code,
                        'is_active' => true,
                    ],
                );

                return $stoCode;
            });
        } catch (Throwable $exception) {
            Log::error('Active STO activation failed', [
                'sto_code_id' => $stoCode->id,
                'sto_code' => $stoCode->code,
                'user_id' => $actor?->id,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }
}
