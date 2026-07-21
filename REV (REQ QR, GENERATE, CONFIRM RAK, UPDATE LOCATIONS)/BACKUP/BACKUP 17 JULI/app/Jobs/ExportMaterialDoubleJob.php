<?php

namespace App\Jobs;

use App\Models\ExportRequest;
use App\Services\ActivityLogService;
use App\Services\ExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExportMaterialDoubleJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public function __construct(
        public int $exportRequestId,
    ) {}

    public function handle(ExportService $exportService, ActivityLogService $activityLog): void
    {
        $exportRequest = ExportRequest::findOrFail($this->exportRequestId);
        $exportRequest = $exportService->generateQueuedMaterialDoubleExport($exportRequest);

        $activityLog->record(
            user: $exportRequest->user,
            action: 'export.material_double.completed',
            subject: $exportRequest,
            metadata: [
                'format' => $exportRequest->format,
                'filters' => $exportRequest->filters,
                'total_rows' => $exportRequest->total_rows,
                'file_name' => $exportRequest->file_name,
            ],
        );
    }

    public function failed(?Throwable $exception): void
    {
        $exportRequest = ExportRequest::find($this->exportRequestId);

        Log::error('Queued material double export failed', [
            'export_request_id' => $this->exportRequestId,
            'user_id' => $exportRequest?->user_id,
            'format' => $exportRequest?->format,
            'exception' => $exception ? $exception::class : null,
        ]);

        app(ActivityLogService::class)->record(
            user: $exportRequest?->user,
            action: 'export.material_double.job_failed',
            subject: $exportRequest,
            metadata: [
                'format' => $exportRequest?->format,
                'filters' => $exportRequest?->filters,
                'exception' => $exception ? $exception::class : null,
            ],
        );
    }
}
