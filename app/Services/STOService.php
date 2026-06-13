<?php

namespace App\Services;

use App\Models\StoCode;
use Illuminate\Support\Facades\DB;

class STOService
{
    public const NO_ACTIVE_STO_MESSAGE = 'Tidak ada STO aktif yang tersedia. Silakan hubungi Admin.';

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

    public function activate(StoCode $stoCode): StoCode
    {
        return DB::transaction(function () use ($stoCode) {
            StoCode::query()->update(['is_active' => false]);
            $stoCode->update(['is_active' => true]);

            return $stoCode->refresh();
        });
    }
}
