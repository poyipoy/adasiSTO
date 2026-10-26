<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MaterialDoubleExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteMaterialDoubleRequest;
use App\Http\Requests\MaterialDoubleGroupRequest;
use App\Http\Requests\MaterialDoubleScanRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\MaterialDoubleValidation;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Services\ActiveStoService;
use App\Services\ActivityLogService;
use App\Services\MaterialDoubleQueryService;
use App\Services\ScanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class MaterialDoubleController extends Controller
{
    public function __construct(
        private ScanService $scanService,
        private ActivityLogService $activityLog,
        private MaterialDoubleQueryService $materialDoubleQuery,
    ) {}

    public function index(): View
    {
        $filterLimit = max((int) config('sto.admin_filter_options_limit', 500), 1);

        return view('admin.material-double', [
            'plants' => Plant::active()->orderBy('name')->limit($filterLimit)->get(),
            'locations' => collect(),
            'materials' => MasterMaterial::active()->orderBy('material_code')->limit($filterLimit)->get(),
            'stoCodes' => StoCode::orderByDesc('is_active')->orderByDesc('created_at')->limit($filterLimit)->pluck('code'),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = $this->duplicateGroupQuery($request->all());

        $search = $request->input('search.value');
        if ($search) {
            $rawSearch = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($searchQuery) use ($rawSearch) {
                $searchQuery->where('scan_results.barcode_material', 'like', "%{$rawSearch}%")
                    ->orWhere('scan_results.barcode_raw', 'like', "%{$rawSearch}%")
                    ->orWhere('scan_results.material_name', 'like', "%{$rawSearch}%")
                    ->orWhere('scan_results.material_code', 'like', "%{$rawSearch}%")
                    ->orWhere('scan_results.shape_name', 'like', "%{$rawSearch}%")
                    ->orWhere('scan_results.lot_number', 'like', "%{$rawSearch}%")
                    ->orWhere('scan_results.sto_code', 'like', "%{$rawSearch}%")
                    ->orWhere('scan_results.keterangan', 'like', "%{$rawSearch}%")
                    ->orWhere('plants.name', 'like', "%{$rawSearch}%")
                    ->orWhere('locations.name', 'like', "%{$rawSearch}%");
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
                $size = in_array($item->shape_code, ['RF', 'RH'])
                    ? "{$item->thickness} x {$item->width} x {$item->length}"
                    : "\u{2300}{$item->diameter} x {$item->length}";

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

    public function locations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
        ]);

        return response()->json([
            'success' => true,
            'data' => Location::active()
                ->where('plant_id', $validated['plant_id'])
                ->orderBy('name')
                ->get(['id', 'plant_id', 'name']),
        ]);
    }

    public function showDuplicateDetail(MaterialDoubleGroupRequest $request): JsonResponse
    {
        $query = $this->detailQuery($request->validated())
            ->with(['plant', 'location', 'user']);

        $search = $request->input('search.value');
        if ($search) {
            $rawSearch = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($searchQuery) use ($rawSearch) {
                $searchQuery->where('barcode_material', 'like', "%{$rawSearch}%")
                    ->orWhere('barcode_raw', 'like', "%{$rawSearch}%")
                    ->orWhere('material_name', 'like', "%{$rawSearch}%")
                    ->orWhere('material_code', 'like', "%{$rawSearch}%")
                    ->orWhere('shape_name', 'like', "%{$rawSearch}%")
                    ->orWhere('lot_number', 'like', "%{$rawSearch}%")
                    ->orWhere('sto_code', 'like', "%{$rawSearch}%")
                    ->orWhere('keterangan', 'like', "%{$rawSearch}%")
                    ->orWhereHas('user', fn ($uQuery) => $uQuery->where('name', 'like', "%{$rawSearch}%"))
                    ->orWhereHas('plant', fn ($pQuery) => $pQuery->where('name', 'like', "%{$rawSearch}%"))
                    ->orWhereHas('location', fn ($lQuery) => $lQuery->where('name', 'like', "%{$rawSearch}%"));
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
                    'lot_number' => $scanResult->lot_number ?: '-',
                    'scan_time' => $scanResult->created_at?->format('Y-m-d H:i:s') ?? '-',
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

        $result = DB::transaction(function () use ($requestedIds, $request, $payload) {
            $groupIds = $this->detailQuery($payload)
                ->lockForUpdate()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values();
            $allowedIds = $groupIds->intersect($requestedIds)->sort()->values();

            if ($requestedIds->all() !== $allowedIds->all()) {
                return ['error' => 'Data yang dipilih tidak sesuai dengan group duplicate ini.'];
            }

            if ($requestedIds->count() >= $groupIds->count()) {
                return ['error' => 'Anda tidak bisa menghapus semua data sekaligus. Sisakan minimal 1 data utama.'];
            }

            foreach ($requestedIds as $scanResultId) {
                $this->scanService->deleteFromMaterialDouble($request->user(), $scanResultId);
            }

            $activeStoId = app(ActiveStoService::class)->active()?->id;
            $validation = MaterialDoubleValidation::query()
                ->where('sto_code_id', $activeStoId)
                ->where('barcode_material', $payload['barcode_material'])
                ->where('plant_id', $payload['plant_id'])
                ->where('location_id', $payload['location_id'])
                ->first();

            if ($validation) {
                $oldValues = $validation->toArray();
                $this->activityLog->recordOrFail(
                    $request->user(),
                    'material_double.validation_cleared',
                    $validation,
                    oldValues: $oldValues,
                );
                $validation->delete();
            }

            return ['deleted_count' => $requestedIds->count()];
        });

        if (isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data duplicate terpilih berhasil dihapus. Status tetap Need Check sampai tombol Valid digunakan.',
            'deleted_count' => $result['deleted_count'],
        ]);
    }

    public function scan(MaterialDoubleScanRequest $request): JsonResponse
    {
        $result = $this->scanService->storeMaterialDoubleScan($request->user(), $request->validated());

        if (! $result['success']) {
            return response()->json(
                collect($result)->except('status')->all(),
                $result['status'] ?? 422
            );
        }

        return response()->json($result, 201);
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['sto_code', 'plant_id', 'location_id', 'material_code', 'date_from', 'date_to', 'status']);
        $fileName = 'Material_Double_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new MaterialDoubleExport($filters), $fileName);
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

        if (! $orderInfo || ! isset($columns[$orderInfo['column']])) {
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
        $activeStoId = app(ActiveStoService::class)->active()?->id;

        return DB::transaction(function () use ($request, $payload, $activeStoId) {
            $groupCount = $this->detailQuery($payload)->lockForUpdate()->count();
            if ($groupCount < 2) {
                throw ValidationException::withMessages([
                    'barcode_material' => 'Group ini tidak lagi memiliki data duplicate untuk divalidasi.',
                ]);
            }

            $validation = MaterialDoubleValidation::updateOrCreate(
                [
                    'sto_code_id' => $activeStoId,
                    'barcode_material' => $payload['barcode_material'],
                    'plant_id' => $payload['plant_id'],
                    'location_id' => $payload['location_id'],
                ],
                [
                    'validated_by' => $request->user()->id,
                    'validated_at' => now(),
                ]
            );

            $this->activityLog->recordOrFail($request->user(), 'material_double.validated', $validation, newValues: [
                'barcode_material' => $validation->barcode_material,
                'plant_id' => $validation->plant_id,
                'location_id' => $validation->location_id,
                'validated_at' => $validation->validated_at?->format('Y-m-d H:i:s'),
            ]);

            return $validation;
        });
    }
}
