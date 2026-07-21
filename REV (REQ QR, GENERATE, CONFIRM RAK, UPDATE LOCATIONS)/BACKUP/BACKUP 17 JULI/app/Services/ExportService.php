<?php

namespace App\Services;

use App\Exports\ScanResultsExport;
use App\Exports\MaterialDoubleExport;
use App\Jobs\ExportScanResultsJob;
use App\Jobs\ExportMaterialDoubleJob;
use App\Models\ExportRequest;
use App\Models\ScanResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;

class ExportService
{
    public function filteredScanResults(array $filters = []): Builder
    {
        $query = ScanResult::query()
            ->with(['user', 'plant', 'location'])
            ->latestFirst();



        if (!empty($filters['plant_id'])) {
            $query->where('scan_results.plant_id', $filters['plant_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('scan_results.location_id', $filters['location_id']);
        }

        if (!empty($filters['location_name'])) {
            $query->whereExists(function ($sub) use ($filters) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('locations')
                    ->whereColumn('locations.id', 'scan_results.location_id')
                    ->where('locations.name', $filters['location_name']);
            });
        }

        if (!empty($filters['user_id'])) {
            $query->where('scan_results.user_id', $filters['user_id']);
        }

        if (!empty($filters['material_code'])) {
            $query->where('scan_results.material_code', $filters['material_code']);
        }

        if (!empty($filters['lot_number'])) {
            $query->where('scan_results.lot_number', 'like', '%' . $filters['lot_number'] . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->where('scan_results.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where('scan_results.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query;
    }

    public function exportFilters(array $input): array
    {
        return collect($input)
            ->only(['plant_id', 'location_id', 'location_name', 'user_id', 'material_code', 'lot_number', 'date_from', 'date_to'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }

    public function queueScanResultsExport(User $user, string $format, array $input): ExportRequest
    {
        $format = $this->normalizeFormat($format);
        $filters = $this->exportFilters($input);
        $extension = $format === 'excel' ? 'xlsx' : 'pdf';

        $exportRequest = ExportRequest::create([
            'user_id' => $user->id,
            'report' => 'scan_results',
            'format' => $format,
            'status' => ExportRequest::STATUS_QUEUED,
            'filters' => $filters,
            'file_disk' => config('sto.export_disk', 'local'),
            'file_name' => 'STO_Scan_Results_' . now()->format('Ymd_His') . '.' . $extension,
            'mime_type' => $format === 'excel'
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : 'application/pdf',
            'queued_at' => now(),
        ]);

        ExportScanResultsJob::dispatch($exportRequest->id);

        return $exportRequest;
    }

    public function recentScanResultExports(User $user, int $limit = 5): Collection
    {
        return ExportRequest::query()
            ->where('user_id', $user->id)
            ->where('report', 'scan_results')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function generateQueuedScanResultsExport(ExportRequest $exportRequest): ExportRequest
    {
        $exportRequest->forceFill([
            'status' => ExportRequest::STATUS_PROCESSING,
            'started_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ])->save();

        try {
            $filters = $exportRequest->filters ?: [];
            $totalRows = (clone $this->filteredScanResults($filters))->count();
            $path = 'exports/scan-results/' . $exportRequest->id . '-' . $exportRequest->file_name;

            if ($exportRequest->format === 'excel') {
                Excel::store(new ScanResultsExport($filters), $path, $exportRequest->file_disk);
            } elseif ($exportRequest->format === 'pdf') {
                $this->storePdfExport($filters, $path, $exportRequest->file_disk);
            } else {
                throw new InvalidArgumentException('Format export tidak valid.');
            }

            if (!Storage::disk($exportRequest->file_disk)->exists($path)) {
                throw new RuntimeException('File export gagal dibuat.');
            }

            $exportRequest->forceFill([
                'status' => ExportRequest::STATUS_COMPLETED,
                'file_path' => $path,
                'total_rows' => $totalRows,
                'completed_at' => now(),
            ])->save();

            return $exportRequest->refresh();
        } catch (Throwable $exception) {
            $exportRequest->forceFill([
                'status' => ExportRequest::STATUS_FAILED,
                'failed_at' => now(),
                'error_message' => 'Export gagal diproses.',
            ])->save();

            throw $exception;
        }
    }

    public function queueMaterialDoubleExport(User $user, string $format, array $input): ExportRequest
    {
        $format = $this->normalizeFormat($format);
        $filters = $this->exportFilters($input);
        
        if ($format !== 'excel') {
            throw new InvalidArgumentException('Hanya format excel yang didukung untuk material double.');
        }

        $exportRequest = ExportRequest::create([
            'user_id' => $user->id,
            'report' => 'material_double',
            'format' => $format,
            'status' => ExportRequest::STATUS_QUEUED,
            'filters' => $filters,
            'file_disk' => config('sto.export_disk', 'local'),
            'file_name' => 'STO_Material_Double_' . now()->format('Ymd_His') . '.xlsx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'queued_at' => now(),
        ]);

        ExportMaterialDoubleJob::dispatch($exportRequest->id);

        return $exportRequest;
    }

    public function generateQueuedMaterialDoubleExport(ExportRequest $exportRequest): ExportRequest
    {
        $exportRequest->forceFill([
            'status' => ExportRequest::STATUS_PROCESSING,
            'started_at' => now(),
            'failed_at' => null,
            'error_message' => null,
        ])->save();

        try {
            $filters = $exportRequest->filters ?: [];
            $path = 'exports/material-double/' . $exportRequest->id . '-' . $exportRequest->file_name;

            Excel::store(new MaterialDoubleExport($filters), $path, $exportRequest->file_disk);

            if (!Storage::disk($exportRequest->file_disk)->exists($path)) {
                throw new RuntimeException('File export gagal dibuat.');
            }

            $exportRequest->forceFill([
                'status' => ExportRequest::STATUS_COMPLETED,
                'file_path' => $path,
                'completed_at' => now(),
            ])->save();

            return $exportRequest->refresh();
        } catch (Throwable $exception) {
            $exportRequest->forceFill([
                'status' => ExportRequest::STATUS_FAILED,
                'failed_at' => now(),
                'error_message' => 'Export gagal diproses.',
            ])->save();

            throw $exception;
        }
    }

    public function recentMaterialDoubleExports(User $user, int $limit = 5): Collection
    {
        return ExportRequest::query()
            ->where('user_id', $user->id)
            ->where('report', 'material_double')
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function serializeExportRequest(ExportRequest $exportRequest): array
    {
        return [
            'id' => $exportRequest->id,
            'format' => $exportRequest->format,
            'status' => $exportRequest->status,
            'file_name' => $exportRequest->file_name,
            'total_rows' => $exportRequest->total_rows,
            'queued_at' => $exportRequest->queued_at?->format('Y-m-d H:i:s'),
            'started_at' => $exportRequest->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $exportRequest->completed_at?->format('Y-m-d H:i:s'),
            'failed_at' => $exportRequest->failed_at?->format('Y-m-d H:i:s'),
            'message' => $this->statusMessage($exportRequest),
            'download_url' => $exportRequest->isCompleted()
                ? ($exportRequest->report === 'material_double'
                    ? route('admin.api.material-double.export.download', $exportRequest)
                    : route('admin.export.scan-results.download', $exportRequest))
                : null,
        ];
    }

    private function normalizeFormat(string $format): string
    {
        $format = strtolower(trim($format));

        if (!in_array($format, ['excel', 'pdf'], true)) {
            throw new InvalidArgumentException('Format export tidak valid.');
        }

        return $format;
    }

    private function storePdfExport(array $filters, string $path, string $disk): void
    {
        $rows = $this->filteredScanResults($filters)
            ->limit((int) config('sto.export_pdf_row_limit', 2000))
            ->get();

        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            throw new RuntimeException('PDF export belum tersedia.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.scan-results-pdf', compact('rows', 'filters'))
            ->setPaper('a4', 'landscape');

        Storage::disk($disk)->put($path, $pdf->output());
    }

    private function statusMessage(ExportRequest $exportRequest): string
    {
        return match ($exportRequest->status) {
            ExportRequest::STATUS_QUEUED => 'Menunggu diproses.',
            ExportRequest::STATUS_PROCESSING => 'Sedang diproses.',
            ExportRequest::STATUS_COMPLETED => 'Export siap diunduh.',
            ExportRequest::STATUS_FAILED => $exportRequest->error_message ?: 'Export gagal diproses.',
            default => 'Status export tidak diketahui.',
        };
    }
}
