<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckDuplicateScanRequest;
use App\Http\Requests\PreviewScanRequest;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\StoreScanRequest;
use App\Http\Requests\StoreSetupRequest;
use App\Models\Location;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Services\ActiveStoService;
use App\Services\OverviewService;
use App\Services\ScanService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ScanController extends Controller
{
    private const RECENT_SCAN_PER_PAGE = 50;

    public function __construct(
        private ScanService $scanService,
        private ActiveStoService $activeStoService,
        private OverviewService $overviewService,
    ) {}

    public function overview(Request $request): View
    {
        $scopeUser = $request->user();
        $scanOverview = $this->overviewService->scanOverview($scopeUser);
        $scanPerDay = $this->overviewService->scanPerDay($scopeUser);
        
        $validatorOverview = $request->user()->isValidator()
            ? $this->overviewService->validatorOverview()
            : null;
        $validationByScanner = $request->user()->isValidator()
            ? $this->overviewService->validationByScanner()
            : collect();

        return view('scan.overview', compact(
            'scanOverview',
            'scanPerDay',
            'validatorOverview',
            'validationByScanner',
        ));
    }

    public function overviewData(Request $request): JsonResponse
    {
        $scopeUser = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'scan_overview' => $this->overviewService->scanOverview($scopeUser),
                'scan_per_day' => $this->overviewService->scanPerDay($scopeUser),
                'validator_overview' => $request->user()->isValidator()
                    ? $this->overviewService->validatorOverview()
                    : null,
                'validation_by_scanner' => $request->user()->isValidator()
                    ? $this->overviewService->validationByScanner()
                    : null,
            ],
        ]);
    }

    public function setup(): View
    {
        $activeSto = $this->activeStoService->active();
        $plants = Plant::active()->orderBy('name')->get();
        $scanContext = session('scan_context');

        return view('scan.setup', compact('activeSto', 'plants', 'scanContext'));
    }

    public function storeSetup(StoreSetupRequest $request)
    {
        $activeSto = $this->activeStoService->active();

        if (!$activeSto) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => ActiveStoService::NO_ACTIVE_STO_MESSAGE], 422);
            }
            return back()->with('error', ActiveStoService::NO_ACTIVE_STO_MESSAGE);
        }

        session([
            'scan_context' => [
                'plant_id' => $request->integer('plant_id'),
                'location_id' => $request->integer('location_id'),
            ],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->to(route('scan.scanner', [], false));
    }

    public function scanner(Request $request): View
    {
        $activeSto = $this->activeStoService->active();
        $scanContext = session('scan_context');

        if (!$activeSto) {
            return view('scan.no-session', ['message' => ActiveStoService::NO_ACTIVE_STO_MESSAGE]);
        }

        if (!$scanContext) {
            return view('scan.no-session', ['message' => 'Silakan setup STO terlebih dahulu sebelum memulai scan.']);
        }

        $plant = Plant::findOrFail($scanContext['plant_id']);
        $location = Location::active()
            ->where('plant_id', $plant->id)
            ->findOrFail($scanContext['location_id']);

        $recentScans = $this->recentScanPaginator($request->user()->id, (int) $request->input('page', 1), $plant->id, $location->id);
        $recentMeta = $this->recentScanMeta($recentScans);
        $totalToday = ScanResult::forUser(auth()->id())
            ->today()
            ->where('plant_id', $plant->id)
            ->where('location_id', $location->id)
            ->count();

        $locations = Location::active()
            ->where('plant_id', $plant->id)
            ->orderBy('name')
            ->get();

        return view('scan.scanner', compact('activeSto', 'plant', 'location', 'locations', 'recentScans', 'recentMeta', 'totalToday'));
    }

    public function historyPage(Request $request): View
    {
        $filterOptions = $this->scanService->historyFilterOptions($request->user());

        return view('scan.results', compact('filterOptions'));
    }

    public function materialSummary(): View
    {
        return view('scan.material-summary', [
            'plants' => Plant::active()->orderBy('name')->get(),
        ]);
    }

    public function materialSummaryData(Request $request): JsonResponse
    {
        $scopeUser = $request->user()->isValidator() ? null : $request->user();
        $query = $this->overviewService->materialSummaryQuery($scopeUser, $request->all());

        $search = $request->input('search.value');
        if ($search) {
            $searchData = \App\Services\BarcodeParserService::normalizeSearch($search);
            $normalizedSearch = str_replace(['%', '_'], ['\\%', '\\_'], $searchData['normalized']);
            $firstPartSearch = str_replace(['%', '_'], ['\\%', '\\_'], $searchData['first_part']);

            $query->where(function ($q) use ($normalizedSearch, $firstPartSearch) {
                $q->where('barcode_material', 'like', "%{$firstPartSearch}%")
                    ->orWhere('barcode_raw', 'like', "%{$normalizedSearch}%")
                    ->orWhere('material_name', 'like', "%{$normalizedSearch}%")
                    ->orWhere('material_code', 'like', "%{$normalizedSearch}%");
            });
        }

        $filteredRecords = \Illuminate\Support\Facades\DB::query()
            ->fromSub((clone $query), 'scanner_material_summary_count')
            ->count();

        $maxLength = max((int) config('sto.datatable_max_length', 100), 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), $maxLength);

        $orderInfo = $request->input('order.0');
        $columns = $request->input('columns');

        if ($orderInfo && isset($columns[$orderInfo['column']])) {
            $columnData = $columns[$orderInfo['column']]['data'];
            $dir = $orderInfo['dir'] === 'asc' ? 'asc' : 'desc';
            $sortableColumns = [
                'barcode_material' => 'barcode_material',
                'material_code' => 'material_code',
                'material_name' => 'material_name',
                'shape_name' => 'shape_name',
                'qty_total' => 'qty_total',
                'scan_count' => 'scan_count',
                'max_created_at' => 'max_created_at',
            ];

            if (isset($sortableColumns[$columnData])) {
                $query->orderBy($sortableColumns[$columnData], $dir);
            } else {
                $query->orderByDesc('max_created_at');
            }
        } else {
            $query->orderByDesc('max_created_at');
        }

        $data = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data->map(function ($item, int $index) use ($filteredRecords, $start) {
                $size = in_array($item->shape_code, ['RF', 'RH'])
                    ? "{$item->thickness} x {$item->width} x {$item->length}"
                    : "⌀{$item->diameter} x {$item->length}";

                return [
                    'no' => $filteredRecords - $start - $index,
                    'barcode_material' => $item->barcode_material,
                    'material_code' => $item->material_code,
                    'material_name' => $item->material_name,
                    'shape_name' => $item->shape_name,
                    'size' => $size,
                    'qty_total' => (int) $item->qty_total,
                    'scan_count' => (int) $item->scan_count,
                ];
            })->values(),
        ]);
    }

    public function locations(Request $request): JsonResponse
    {
        $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
        ]);

        $locations = Location::active()
            ->where('plant_id', $request->integer('plant_id'))
            ->orderBy('name')
            ->get(['id', 'plant_id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    public function storeLocation(StoreLocationRequest $request): JsonResponse
    {
        $location = Location::create([
            'plant_id' => $request->integer('plant_id'),
            'name' => $request->string('name')->toString(),
            'is_active' => true,
            'created_by_user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location/Rack berhasil ditambahkan.',
            'data' => [
                'id' => $location->id,
                'plant_id' => $location->plant_id,
                'name' => $location->name,
            ],
        ], 201);
    }

    public function destroyLocation(Request $request, int $id): JsonResponse
    {
        $location = Location::findOrFail($id);

        if (ScanResult::where('location_id', $location->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa menghapus lokasi karena sudah digunakan untuk scan.',
            ], 422);
        }

        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location/Rack berhasil dihapus.',
        ]);
    }

    public function preview(PreviewScanRequest $request): JsonResponse
    {
        $result = $this->scanService->preview($request->string('qr')->toString());

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        unset($result['valid']);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function checkDuplicate(CheckDuplicateScanRequest $request): JsonResponse
    {
        $activeSto = $this->activeStoService->active();

        if (!$activeSto) {
            return response()->json([
                'success' => false,
                'message' => ActiveStoService::NO_ACTIVE_STO_MESSAGE,
            ], 422);
        }

        $duplicate = $this->scanService->isDuplicate($request->string('barcode_material')->toString());

        return response()->json([
            'success' => true,
            'duplicate' => $duplicate,
            'message' => $duplicate ? 'Barcode sudah pernah discan sebelumnya.' : null,
        ]);
    }
    
    public function store(StoreScanRequest $request): JsonResponse
    {
        $result = $this->scanService->store($request->user(), $request->validated());

        if (!$result['success']) {
            return response()->json(
                collect($result)->except('status')->all(),
                $result['status'] ?? 422
            );
        }

        return response()->json($result);
    }

    public function history(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->input('per_page', 25), 1), 100);
        $page = max((int) $request->input('page', 1), 1);

        $paginator = $this->scanService
            ->historyQuery($request->user(), $request->only(['date_from', 'date_to', 'search', 'barcode_material', 'material_code', 'plant_id', 'location_id']))
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $paginator->getCollection()
                ->map(fn (ScanResult $scanResult) => $this->scanService->serializeScan($scanResult))
                ->values(),
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $scanContext = session('scan_context');
        $plantId = $scanContext ? $scanContext['plant_id'] : null;
        $locationId = $scanContext ? $scanContext['location_id'] : null;

        $paginator = $this->recentScanPaginator($request->user()->id, (int) $request->input('page', 1), $plantId, $locationId);
        $totalToday = ScanResult::forUser($request->user()->id)
            ->today()
            ->when($plantId, fn($q) => $q->where('plant_id', $plantId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->count();

        return response()->json([
            'success' => true,
            'data' => $paginator->getCollection()
                ->map(fn (ScanResult $scanResult) => $this->scanService->serializeScan($scanResult))
                ->values(),
            'meta' => $this->recentScanMeta($paginator),
            'total_today' => $totalToday,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->scanService->deleteForScanner($request->user(), $id);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Scan berhasil dihapus.',
        ]);
    }

    private function recentScanPaginator(int $userId, int $page, ?int $plantId = null, ?int $locationId = null)
    {
        return ScanResult::query()
            ->with(['plant', 'location'])
            ->forUser($userId)
            ->today()
            ->when($plantId, fn ($query) => $query->where('plant_id', $plantId))
            ->when($locationId, fn ($query) => $query->where('location_id', $locationId))
            ->latestFirst()
            ->paginate(self::RECENT_SCAN_PER_PAGE, ['*'], 'page', max($page, 1));
    }

    private function recentScanMeta($paginator): array
    {
        return [
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}
