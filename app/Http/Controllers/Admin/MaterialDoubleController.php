<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteMaterialDoubleRequest;
use App\Http\Requests\MaterialDoubleGroupRequest;
use App\Models\Location;
use App\Models\MasterMaterial;
use App\Models\MaterialDoubleValidation;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Services\ActivityLogService;
use App\Services\ScanService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaterialDoubleController extends Controller
{
    public function __construct(
        private ScanService $scanService,
        private ActivityLogService $activityLog,
    ) {}

    public function index(): View
    {
        $filterLimit = max((int) config('sto.admin_filter_options_limit', 500), 1);

        return view('admin.material-double', [
            'plants' => Plant::active()->orderBy('name')->limit($filterLimit)->get(),
            'locations' => Location::active()->with(['plant'])->orderBy('name')->limit($filterLimit)->get(),
            'materials' => MasterMaterial::active()->orderBy('material_code')->limit($filterLimit)->get(),
            'stoCodes' => StoCode::orderByDesc('is_active')->orderByDesc('created_at')->limit($filterLimit)->pluck('code'),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = $this->duplicateGroupQuery($request);

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

        foreach ($requestedIds as $scanResultId) {
            $this->scanService->deleteByAdmin($request->user(), $scanResultId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data duplicate terpilih berhasil dihapus.',
            'deleted_count' => $requestedIds->count(),
        ]);
    }

    private function duplicateGroupQuery(Request $request): Builder
    {
        $query = ScanResult::query()
            ->leftJoin('plants', 'scan_results.plant_id', '=', 'plants.id')
            ->leftJoin('locations', 'scan_results.location_id', '=', 'locations.id')
            ->leftJoin('material_double_validations as mdv', function ($join) {
                $join->on('mdv.barcode_material', '=', 'scan_results.barcode_material')
                    ->on('mdv.plant_id', '=', 'scan_results.plant_id')
                    ->on('mdv.location_id', '=', 'scan_results.location_id');
            })
            ->selectRaw('
                scan_results.barcode_material,
                scan_results.material_name,
                scan_results.shape_code,
                scan_results.shape_name,
                scan_results.thickness,
                scan_results.width,
                scan_results.diameter,
                scan_results.length,
                scan_results.plant_id,
                scan_results.location_id,
                plants.name as plant_name,
                locations.name as location_name,
                COUNT(*) as duplicate_count,
                MAX(mdv.validated_at) as validated_at
            ')
            ->groupBy(
                'scan_results.barcode_material',
                'scan_results.material_name',
                'scan_results.shape_code',
                'scan_results.shape_name',
                'scan_results.thickness',
                'scan_results.width',
                'scan_results.diameter',
                'scan_results.length',
                'scan_results.plant_id',
                'scan_results.location_id',
                'plants.name',
                'locations.name',
            )
            ->havingRaw('COUNT(*) > 1');

        $this->applyScanFilters($query, $request->all(), tablePrefix: 'scan_results.');

        return $query;
    }

    private function detailQuery(array $filters): Builder
    {
        $query = ScanResult::query()
            ->where('barcode_material', $filters['barcode_material'])
            ->where('plant_id', $filters['plant_id'])
            ->where('location_id', $filters['location_id']);

        $this->applyScanFilters($query, $filters);

        return $query;
    }

    private function applyScanFilters(Builder $query, array $filters, string $tablePrefix = ''): void
    {
        if (!empty($filters['sto_code'])) {
            $query->where($tablePrefix . 'sto_code', $filters['sto_code']);
        }

        if (!empty($filters['plant_id'])) {
            $query->where($tablePrefix . 'plant_id', $filters['plant_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where($tablePrefix . 'location_id', $filters['location_id']);
        }

        if (!empty($filters['material_code'])) {
            $query->where($tablePrefix . 'material_code', $filters['material_code']);
        }

        if (!empty($filters['date_from'])) {
            $query->where($tablePrefix . 'created_at', '>=', $filters['date_from'] . ' 00:00:00');
        }

        if (!empty($filters['date_to'])) {
            $query->where($tablePrefix . 'created_at', '<=', $filters['date_to'] . ' 23:59:59');
        }
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
}
