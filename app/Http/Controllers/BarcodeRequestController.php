<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarcodeRequestRequest;
use App\Models\BarcodeRequest;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Services\ActiveStoService;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class BarcodeRequestController extends Controller
{
    public function __construct(
        private ActiveStoService $activeStoService,
        private ActivityLogService $activityLog
    ) {}

    /**
     * Show the barcode request form and history.
     */
    public function index(): View
    {
        $materials = MasterMaterial::active()->orderBy('material_name')->get();
        $plants = Plant::active()->orderBy('name')->get();

        return view('scan.barcode-request', [
            'materials' => $materials,
            'plants' => $plants,
        ]);
    }

    /**
     * Store a new barcode request.
     */
    public function store(StoreBarcodeRequestRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $material = MasterMaterial::where('material_code', $validated['material_code'])->first();
        
        $barcodeRequest = new BarcodeRequest();
        $barcodeRequest->user_id = Auth::id();
        $barcodeRequest->sto_code_id = $this->activeStoService->active()?->id;
        $barcodeRequest->plant_id = $validated['plant_id'];
        $barcodeRequest->location_id = $validated['location_id'];
        $barcodeRequest->material_code = $validated['material_code'];
        $barcodeRequest->material_name = $material->material_name;
        $barcodeRequest->shape_code = $validated['shape_code'];
        $barcodeRequest->shape_name = $validated['shape_code'] === 'RR' ? 'Round' : ($validated['shape_code'] === 'RH' ? 'Hollow' : 'Flat');
        $barcodeRequest->thickness = $validated['thickness'] ?? null;
        $barcodeRequest->width = $validated['width'] ?? null;
        $barcodeRequest->diameter = $validated['diameter'] ?? null;
        $barcodeRequest->length = $validated['length'];
        $barcodeRequest->lot_number = $validated['lot_number'];
        $barcodeRequest->status = 'pending';
        
        $barcodeRequest->save();

        // Log the activity
        $this->activityLog->record(
            user: Auth::user(),
            action: 'create_barcode_request',
            subject: $barcodeRequest,
            newValues: $barcodeRequest->toArray()
        );

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil dibuat'
        ]);
    }

    /**
     * Get DataTable of user's barcode requests.
     */
    public function datatable(): JsonResponse
    {
        $query = BarcodeRequest::with(['plant', 'location'])
            ->where('user_id', Auth::id())
            ->select('barcode_requests.*');

        return DataTables::of($query)
            ->addColumn('material_info', function (BarcodeRequest $request) {
                return $request->material_name . ' (' . $request->material_code . ')';
            })
            ->addColumn('plant_name', function (BarcodeRequest $request) {
                return $request->plant?->name ?? '-';
            })
            ->addColumn('location_name', function (BarcodeRequest $request) {
                return $request->location?->name ?? '-';
            })
            ->editColumn('created_at', function (BarcodeRequest $request) {
                return $request->created_at->format('Y-m-d H:i:s');
            })
            ->make(true);
    }

    /**
     * Cancel a pending barcode request.
     */
    public function destroy(int $id): JsonResponse
    {
        $barcodeRequest = BarcodeRequest::findOrFail($id);

        if ($barcodeRequest->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses ke request ini.'], 403);
        }

        if ($barcodeRequest->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Hanya request pending milik Anda yang bisa dibatalkan.'], 403);
        }

        $oldValues = $barcodeRequest->toArray();
        $barcodeRequest->delete();

        // Log the activity
        $this->activityLog->record(
            user: Auth::user(),
            action: 'cancel_barcode_request',
            subject: $barcodeRequest,
            oldValues: $oldValues
        );

        return response()->json([
            'success' => true,
            'message' => 'Request berhasil dibatalkan'
        ]);
    }

    /**
     * Get dimension suggestions based on scan & request history for a material.
     */
    public function suggestions(Request $request): JsonResponse
    {
        $materialCode = $request->query('material_code');
        if (! $materialCode) {
            return response()->json(['success' => false, 'message' => 'Material code required'], 422);
        }

        $material = MasterMaterial::where('material_code', strtoupper($materialCode))->first();
        if (! $material) {
            return response()->json(['success' => false, 'message' => 'Material not found'], 404);
        }

        // Search latest from ScanResult across all scopes
        $latestScan = ScanResult::withoutGlobalScope('active_sto')
            ->where('material_code', strtoupper($materialCode))
            ->whereNotNull('shape_code')
            ->orderByDesc('created_at')
            ->first();

        // Also check BarcodeRequest
        $latestReq = BarcodeRequest::where('material_code', strtoupper($materialCode))
            ->whereNotNull('shape_code')
            ->orderByDesc('created_at')
            ->first();

        $suggestion = null;
        if ($latestScan && $latestReq) {
            $suggestion = $latestScan->created_at->gt($latestReq->created_at) ? $latestScan : $latestReq;
        } else {
            $suggestion = $latestScan ?? $latestReq;
        }

        // Get up to 5 distinct dimension combinations from scan results & requests
        $scansHistory = ScanResult::withoutGlobalScope('active_sto')
            ->where('material_code', strtoupper($materialCode))
            ->whereNotNull('shape_code')
            ->select('shape_code', 'thickness', 'width', 'diameter', 'length', 'lot_number')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $reqsHistory = BarcodeRequest::where('material_code', strtoupper($materialCode))
            ->whereNotNull('shape_code')
            ->select('shape_code', 'thickness', 'width', 'diameter', 'length', 'lot_number')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $combined = $scansHistory->concat($reqsHistory)->map(function ($item) {
            return [
                'shape_code' => $item->shape_code,
                'thickness'  => $item->thickness,
                'width'      => $item->width,
                'diameter'   => $item->diameter,
                'length'     => $item->length,
                'lot_number' => $item->lot_number,
            ];
        })->unique(function ($item) {
            return implode('_', [
                $item['shape_code'],
                $item['thickness'] ?? 'null',
                $item['width'] ?? 'null',
                $item['diameter'] ?? 'null',
                $item['length'] ?? 'null',
            ]);
        })->values()->take(5);

        // Get up to 5 distinct lot numbers from scan results & requests
        $scansLots = ScanResult::withoutGlobalScope('active_sto')
            ->where('material_code', strtoupper($materialCode))
            ->whereNotNull('lot_number')
            ->where('lot_number', '!=', '')
            ->orderByDesc('created_at')
            ->limit(20)
            ->pluck('lot_number');

        $reqsLots = BarcodeRequest::where('material_code', strtoupper($materialCode))
            ->whereNotNull('lot_number')
            ->where('lot_number', '!=', '')
            ->orderByDesc('created_at')
            ->limit(20)
            ->pluck('lot_number');

        $lotHistory = $scansLots->concat($reqsLots)
            ->filter()
            ->unique()
            ->values()
            ->take(5);

        return response()->json([
            'success'       => true,
            'material_code' => $material->material_code,
            'material_name' => $material->material_name,
            'suggestion'    => $suggestion ? [
                'shape_code' => $suggestion->shape_code,
                'thickness'  => $suggestion->thickness,
                'width'      => $suggestion->width,
                'diameter'   => $suggestion->diameter,
                'length'     => $suggestion->length,
                'lot_number' => $suggestion->lot_number,
            ] : null,
            'history'       => $combined,
            'lot_history'   => $lotHistory,
        ]);
    }
}
