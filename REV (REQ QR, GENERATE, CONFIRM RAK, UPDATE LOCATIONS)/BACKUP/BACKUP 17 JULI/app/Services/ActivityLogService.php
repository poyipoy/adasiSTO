<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActivityLogService
{
    public function record(
        ?User $user,
        string $action,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?Request $request = null,
    ): void {
        try {
            $request ??= request();

            ActivityLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'old_values' => $oldValues ?: null,
                'new_values' => $newValues ?: null,
                'metadata' => $metadata ?: null,
                'ip_address' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Activity log write failed', [
                'action' => $action,
                'user_id' => $user?->id,
                'subject_type' => $subject ? $subject::class : null,
                'subject_id' => $subject?->getKey(),
                'exception' => $exception::class,
            ]);
        }
    }
}
