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
use App\Services\STOService;
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
        private STOService $stoService,
    ) {}

    public function setup(): View
    {
        $activeSto = $this->stoService->active();
        $plants = Plant::active()->orderBy('name')->get();
        $scanContext = session('scan_context');

        return view('scan.setup', compact('activeSto', 'plants', 'scanContext'));
    }

    public function storeSetup(StoreSetupRequest $request)
    {
        $activeSto = $this->stoService->active();

        if (!$activeSto) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => STOService::NO_ACTIVE_STO_MESSAGE], 422);
            }
            return back()->with('error', STOService::NO_ACTIVE_STO_MESSAGE);
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
        $activeSto = $this->stoService->active();
        $scanContext = session('scan_context');

        if (!$activeSto) {
            return view('scan.no-session', ['message' => STOService::NO_ACTIVE_STO_MESSAGE]);
        }

        if (!$scanContext) {
            return view('scan.no-session', ['message' => 'Silakan setup STO terlebih dahulu sebelum memulai scan.']);
        }

        $plant = Plant::findOrFail($scanContext['plant_id']);
        $location = Location::active()
            ->forUser(auth()->id())
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
            ->forUser(auth()->id())
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

    public function locations(Request $request): JsonResponse
    {
        $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
        ]);

        $locations = Location::active()
            ->forUser($request->user()->id)
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
            'user_id' => $request->user()->id,
            'plant_id' => $request->integer('plant_id'),
            'name' => $request->string('name')->toString(),
            'is_active' => true,
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

        if ((int) $location->user_id !== (int) $request->user()->id) {
            Log::warning('Unauthorized location delete attempt', [
                'user_id' => $request->user()->id,
                'location_id' => $location->id,
            ]);

            abort(403);
        }

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
        $activeSto = $this->stoService->active();

        if (!$activeSto) {
            return response()->json([
                'success' => false,
                'message' => STOService::NO_ACTIVE_STO_MESSAGE,
            ], 422);
        }

        $duplicate = $this->scanService->isDuplicate($request->string('barcode_material')->toString(), $activeSto->code);

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
