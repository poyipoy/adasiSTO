<?php

namespace App\Jobs;

use App\Models\ScanResult;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecalculateScanSummaryJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function __construct(
        public array $filters = [],
        public ?int $userId = null,
    ) {}

    public function handle(ActivityLogService $activityLog): void
    {
        $query = ScanResult::query();

        if (!empty($this->filters['sto_code'])) {
            $query->where('sto_code', $this->filters['sto_code']);
        }

        if (!empty($this->filters['plant_id'])) {
            $query->where('plant_id', $this->filters['plant_id']);
        }

        $activityLog->record(
            user: $this->actor(),
            action: 'scan_summary.recalculation.job_ready',
            metadata: [
                'filters' => $this->filters,
                'estimated_rows' => $query->count(),
                'summary_table_exists' => false,
            ],
        );
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Queued scan summary recalculation failed', [
            'user_id' => $this->userId,
            'exception' => $exception ? $exception::class : null,
        ]);

        app(ActivityLogService::class)->record(
            user: $this->actor(),
            action: 'scan_summary.recalculation.job_failed',
            metadata: [
                'filters' => $this->filters,
                'exception' => $exception ? $exception::class : null,
            ],
        );
    }

    private function actor(): ?User
    {
        return $this->userId ? User::find($this->userId) : null;
    }
}
