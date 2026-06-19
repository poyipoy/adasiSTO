<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteMaterialDoubleRequest;
use App\Http\Requests\MaterialDoubleGroupRequest;
use App\Http\Requests\MaterialDoubleScanRequest;
use App\Models\ExportRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\MaterialDoubleValidation;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Services\ActivityLogService;
use App\Services\ExportService;
use App\Services\MaterialDoubleQueryService;
use App\Services\ScanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class MaterialDoubleController extends Controller
{
    public function __construct(
        private ScanService $scanService,
        private ExportService $exportService,
        private ActivityLogService $activityLog,
        private MaterialDoubleQueryService $materialDoubleQuery,
    ) {}

    public function index(): View
    {
        $filterLimit = max((int) config('sto.admin_filter_options_limit', 500), 1);

        return view('admin.material-double', [
            'plants' => Plant::active()->orderBy('name')->limit($filterLimit)->get(),
            'locations' => Location::active()->select('name')->distinct()->orderBy('name')->limit($filterLimit)->get(),
            'materials' => MasterMaterial::active()->orderBy('material_code')->limit($filterLimit)->get(),
            'stoCodes' => StoCode::orderByDesc('is_active')->orderByDesc('created_at')->limit($filterLimit)->pluck('code'),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = $this->duplicateGroupQuery($request->all());

        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('scan_results.barcode_material', 'like', "%{$search}%")
                    ->orWhere('scan_results.material_name', 'like', "%{$search}%")
                    ->orWhere('scan_results.material_code', 'like', "%{$search}%")
                    ->orWhere('scan_results.shape_name', 'like', "%{$search}%")
                    ->orWhere('plants.name', 'like', "%{$search}%")
                    ->orWhere('locations.name', 'like', "%{$search}%");
            });
        }

        $filteredRecords = DB::query()
            ->fromSub((clone $query), 'material_double_count')
            ->count();

        $maxLength = max((int) config('sto.datatable_max_length', 100), 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), $maxLength);

        $this->applyGroupOrdering($query, $request);

        $data = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data->map(function ($item, int $index) use ($filteredRecords, $start) {
                $size = $item->shape_code === 'RF'
                    ? "{$item->thickness} x {$item->width} x {$item->length}"
                    : "⌀{$item->diameter} x {$item->length}";

                return [
                    'no' => $filteredRecords - $start - $index,
                    'barcode_material' => $item->barcode_material,
                    'material_name' => $item->material_name,
                    'shape_name' => $item->shape_name,
                    'size' => $size,
                    'plant_id' => (int) $item->plant_id,
                    'plant' => $item->plant_name ?: '-',
                    'location_id' => (int) $item->location_id,
                    'location' => $item->location_name ?: '-',
                    'duplicate_count' => (int) $item->duplicate_count,
                    'is_validated' => $item->validated_at !== null,
                    'validated_at' => $item->validated_at,
                    'validated_by_name' => $item->validated_by_name,
                ];
            })->values(),
        ]);
    }

    public function showDuplicateDetail(MaterialDoubleGroupRequest $request): JsonResponse
    {
        $query = $this->detailQuery($request->validated())
            ->with(['plant', 'location', 'user']);

        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%")
                    ->orWhere('shape_name', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%");
            });
        }

        $records = (clone $query)->count();
        $maxLength = max((int) config('sto.datatable_max_length', 100), 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), $maxLength);

        $rows = $query->latestFirst()->skip($start)->take($length)->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $records,
            'recordsFiltered' => $records,
            'data' => $rows->map(function (ScanResult $scanResult, int $index) use ($records, $start) {
                return [
                    'id' => $scanResult->id,
                    'no' => $records - $start - $index,
                    'barcode_material' => $scanResult->barcode_material,
                    'material_name' => $scanResult->material_name,
                    'shape_name' => $scanResult->shape_name,
                    'user_name' => $scanResult->user ? $scanResult->user->name : '-',
                ];
            })->values(),
        ]);
    }

    public function validateDuplicate(MaterialDoubleGroupRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $this->markDuplicateValidated($request, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Duplicate QR berhasil diverifikasi.',
        ]);
    }

    public function deleteSelected(DeleteMaterialDoubleRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $requestedIds = collect($payload['ids'])->map(fn ($id) => (int) $id)->unique()->sort()->values();

        $allowedIds = $this->detailQuery($payload)
            ->whereIn('id', $requestedIds->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values();

        if ($requestedIds->all() !== $allowedIds->all()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dipilih tidak sesuai dengan group duplicate ini.',
            ], 422);
        }

        $totalInGroup = $this->detailQuery($payload)->count();
        if ($requestedIds->count() >= $totalInGroup) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menghapus semua data sekaligus. Sisakan minimal 1 data utama.',
            ], 422);
        }

        foreach ($requestedIds as $scanResultId) {
            $this->scanService->deleteByAdmin($request->user(), $scanResultId);
        }

        $this->markDuplicateValidated($request, $payload);

        return response()->json([
            'success' => true,
            'message' => 'Data duplicate terpilih berhasil dihapus dan duplicate QR berhasil diverifikasi.',
            'deleted_count' => $requestedIds->count(),
        ]);
    }

    public function scan(MaterialDoubleScanRequest $request): JsonResponse
    {
        $result = $this->scanService->storeMaterialDoubleScan($request->user(), $request->validated());

        if (!$result['success']) {
            return response()->json(
                collect($result)->except('status')->all(),
                $result['status'] ?? 422
            );
        }

        return response()->json($result, 201);
    }

    public function queueExport(Request $request): JsonResponse
    {
        try {
            $exportRequest = $this->exportService->queueMaterialDoubleExport($request->user(), 'excel', $request->all());

            $this->activityLog->record($request->user(), 'export.material_double.requested', $exportRequest, metadata: [
                'format' => $exportRequest->format,
                'filters' => $exportRequest->filters,
                'async' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Export sedang diproses. File akan tersedia saat status selesai.',
                'data' => $this->exportService->serializeExportRequest($exportRequest),
            ], 202);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        } catch (Throwable $exception) {
            Log::error('Async material double export queue failed', [
                'user_id' => $request->user()?->id,
                'exception' => $exception::class,
            ]);

            $this->activityLog->record($request->user(), 'export.material_double.failed', metadata: [
                'filters' => $this->exportService->exportFilters($request->all()),
                'async' => true,
                'exception' => $exception::class,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Export gagal dimulai.',
            ], 500);
        }
    }

    public function exportStatus(Request $request): JsonResponse
    {
        $exports = $this->exportService
            ->recentMaterialDoubleExports($request->user())
            ->map(fn (ExportRequest $exportRequest) => $this->exportService->serializeExportRequest($exportRequest))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $exports,
        ]);
    }

    public function downloadExport(Request $request, ExportRequest $exportRequest)
    {
        abort_unless($exportRequest->user_id === $request->user()->id, 403);
        abort_unless($exportRequest->isCompleted(), 404);
        abort_unless($exportRequest->file_path && Storage::disk($exportRequest->file_disk)->exists($exportRequest->file_path), 404);

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk($exportRequest->file_disk);

        return $disk->download(
            $exportRequest->file_path,
            $exportRequest->file_name,
            ['Content-Type' => $exportRequest->mime_type ?: 'application/octet-stream'],
        );
    }

    public function duplicateGroupQuery(array $filters): Builder
    {
        return $this->materialDoubleQuery->duplicateGroupQuery($filters);
    }

    private function detailQuery(array $filters): Builder
    {
        $query = ScanResult::query()
            ->where('barcode_material', $filters['barcode_material'])
            ->where('plant_id', $filters['plant_id'])
            ->where('location_id', $filters['location_id']);

        $this->materialDoubleQuery->applyScanFilters($query, $filters);

        return $query;
    }

    private function applyGroupOrdering(Builder $query, Request $request): void
    {
        $orderInfo = $request->input('order.0');
        $columns = $request->input('columns');

        if (!$orderInfo || !isset($columns[$orderInfo['column']])) {
            $query->orderByDesc('duplicate_count')->orderByDesc('scan_results.barcode_material');

            return;
        }

        $columnData = $columns[$orderInfo['column']]['data'];
        $dir = $orderInfo['dir'] === 'asc' ? 'asc' : 'desc';

        $sortableColumns = [
            'barcode_material' => 'scan_results.barcode_material',
            'material_name' => 'scan_results.material_name',
            'shape_name' => 'scan_results.shape_name',
            'plant' => 'plants.name',
            'location' => 'locations.name',
            'duplicate_count' => 'duplicate_count',
        ];

        if (isset($sortableColumns[$columnData])) {
            $query->orderBy($sortableColumns[$columnData], $dir);
        } else {
            $query->orderByDesc('duplicate_count');
        }
    }

    private function markDuplicateValidated(Request $request, array $payload): MaterialDoubleValidation
    {
        $validation = MaterialDoubleValidation::updateOrCreate(
            [
                'barcode_material' => $payload['barcode_material'],
                'plant_id' => $payload['plant_id'],
                'location_id' => $payload['location_id'],
            ],
            [
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
            ]
        );

        $this->activityLog->record($request->user(), 'material_double.validated', $validation, newValues: [
            'barcode_material' => $validation->barcode_material,
            'plant_id' => $validation->plant_id,
            'location_id' => $validation->location_id,
            'validated_at' => $validation->validated_at?->format('Y-m-d H:i:s'),
        ]);

        return $validation;
    }
}
