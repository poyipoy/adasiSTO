<?php

namespace App\Services;

use App\Models\StoCode;
use App\Models\User;

class STOService
{
    public const NO_ACTIVE_STO_MESSAGE = ActiveStoService::NO_ACTIVE_STO_MESSAGE;

    public function __construct(private ActiveStoService $activeStoService) {}

    public function active(): ?StoCode
    {
        return $this->activeStoService->active();
    }

    public function requireActive(): StoCode
    {
        return $this->activeStoService->requireActive();
    }

    public function activate(StoCode $stoCode, ?User $actor = null): StoCode
    {
        return $this->activeStoService->activate($stoCode, $actor);
    }
}
