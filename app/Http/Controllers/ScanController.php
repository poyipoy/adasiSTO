<?php

namespace App\Http\Controllers;

use App\DTOs\BarcodeResult;
use App\Models\Location;
use App\Models\MasterKeterangan;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\ScanResultLog;
use App\Models\StoSession;
use App\Models\User;
use App\Services\BarcodeParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function __construct(
        private BarcodeParser $barcodeParser
    ) {}

    /**
     * Setup page — choose PIC, Plant, Location before scanning.
     * STO Code is auto-generated.
     */
    public function setup(): View
    {
        $plants = Plant::active()->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // Get active session for this user if exists
        $activeSession = StoSession::where('user_id', auth()->id())
            ->active()
            ->with('plant')
            ->latest()
            ->first();

        return view('scan.setup', compact('plants', 'users', 'activeSession'));
    }

    /**
     * Store STO session setup.
     */
    public function storeSetup(Request $request)
    {
        $validated = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'location_id' => 'required|exists:locations,id',
        ]);

        $picUser = auth()->user();

        // Auto-generate STO Code: STO{DD}{MM}
        $stoCode = $this->generateStoCode();

        // Create STO session
        $session = StoSession::create([
            'user_id' => $picUser->id,
            'sto_code' => $stoCode,
            'plant_id' => $validated['plant_id'],
            'pic' => $picUser->name,
            'status' => 'active',
        ]);

        // Store location in session for the scanner page
        session([
            'sto_session_id' => $session->id,
            'location_id' => $validated['location_id'],
        ]);

        return redirect()->route('scan.index')->with('success', 'Sesi STO berhasil dimulai! Kode: ' . $stoCode);
    }

    /**
     * Auto-generate STO Code.
     * Format: STO{DD}{MM}-{sequence}
     * Example: STO1006-001, STO1006-002
     */
    private function generateStoCode(): string
    {
        return 'STO' . now()->format('dm'); // e.g. STO1006
    }

    /**
     * Scanner page with camera viewfinder.
     */
    public function index(): View
    {
        $sessionId = session('sto_session_id');

        if (!$sessionId) {
            return view('scan.no-session');
        }

        $stoSession = StoSession::with('plant')->find($sessionId);

        if (!$stoSession) {
            session()->forget(['sto_session_id', 'location_id']);
            return view('scan.no-session');
        }

        $location = Location::findOrFail(session('location_id'));
        $keteranganList = MasterKeterangan::active()->pluck('name');
        $locations = Location::where('plant_id', $stoSession->plant_id)->get();

        // Recent scans (last 10) for this user in this session
        $recentScans = ScanResult::where('user_id', auth()->id())
            ->where('sto_session_id', $sessionId)
            ->latestFirst()
            ->limit(10)
            ->get();

        // Stats
        $totalToday = ScanResult::forUser(auth()->id())->today()->count();
        $totalSession = ScanResult::forUser(auth()->id())
            ->where('sto_session_id', $sessionId)
            ->count();

        return view('scan.scanner', compact(
            'stoSession',
            'location',
            'locations',
            'keteranganList',
            'recentScans',
            'totalToday',
            'totalSession'
        ));
    }

    /**
     * Process barcode scan via AJAX.
     * Lot is auto-filled from STO Code.
     */
    public function storeScan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:100',
            'location_id' => 'required|exists:locations,id',
            'qty' => 'nullable|integer|min:1',
        ]);

        $sessionId = session('sto_session_id');
        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi STO tidak ditemukan. Silakan setup ulang.',
            ], 422);
        }

        $stoSession = StoSession::findOrFail($sessionId);

        // Parse barcode
        $result = $this->barcodeParser->parse($validated['barcode']);

        if (!$result->isValid) {
            return response()->json([
                'success' => false,
                'message' => $result->errorMessage,
            ], 422);
        }

        // Lot = STO Code (auto-filled)
        $lot = $stoSession->sto_code;

        // Store scan result
        $scanResult = ScanResult::create([
            'user_id' => auth()->id(),
            'sto_session_id' => $stoSession->id,
            'plant_id' => $stoSession->plant_id,
            'location_id' => $validated['location_id'],
            ...$result->toArray(),
            'qty' => $validated['qty'] ?? 1,
            'lot' => $lot,
            'scan_time' => now(),
            'keterangan' => 'OK',
        ]);

        // Log creation
        ScanResultLog::create([
            'scan_result_id' => $scanResult->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'new_values' => $scanResult->toArray(),
        ]);

        $scanResult->load('location');

        return response()->json([
            'success' => true,
            'message' => 'Scan berhasil disimpan!',
            'data' => [
                'id' => $scanResult->id,
                'barcode' => $scanResult->barcode_material,
                'material' => $scanResult->material_name,
                'shape' => $scanResult->shape_name,
                'size' => $scanResult->size,
                'lot' => $scanResult->lot,
                'qty' => $scanResult->qty,
                'keterangan' => $scanResult->keterangan,
                'scan_time' => $scanResult->scan_time->format('H:i:s'),
                'location' => $scanResult->location->name,
            ],
        ]);
    }

    /**
     * User's scan results page with DataTable.
     */
    public function results(): View
    {
        $keteranganList = MasterKeterangan::active()->pluck('name');

        $totalToday = ScanResult::forUser(auth()->id())->today()->count();

        $activeSession = StoSession::where('user_id', auth()->id())
            ->active()
            ->with('plant')
            ->latest()
            ->first();

        $totalSession = 0;
        $plantName = '-';
        $locationCount = 0;

        if ($activeSession) {
            $totalSession = ScanResult::forUser(auth()->id())
                ->where('sto_session_id', $activeSession->id)
                ->count();
            $plantName = $activeSession->plant->name;
            $locationCount = ScanResult::forUser(auth()->id())
                ->where('sto_session_id', $activeSession->id)
                ->distinct('location_id')
                ->count('location_id');
        }

        return view('scan.results', compact(
            'keteranganList',
            'totalToday',
            'totalSession',
            'plantName',
            'locationCount'
        ));
    }

    /**
     * Server-side DataTable for user's scan results.
     */
    public function datatable(Request $request): JsonResponse
    {
        $query = ScanResult::with(['location', 'stoSession'])
            ->forUser(auth()->id())
            ->latestFirst();

        return $this->buildDatatable($query, $request);
    }

    /**
     * Update keterangan for a scan result (user can only update their own).
     */
    public function updateKeterangan(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'keterangan' => 'required|string|max:100',
        ]);

        $scanResult = ScanResult::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $oldValues = $scanResult->only(['keterangan']);
        $scanResult->update(['keterangan' => $validated['keterangan']]);

        ScanResultLog::create([
            'scan_result_id' => $scanResult->id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'old_values' => $oldValues,
            'new_values' => ['keterangan' => $validated['keterangan']],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Keterangan berhasil diupdate!',
        ]);
    }

    /**
     * Get locations by plant (AJAX).
     */
    public function getLocations(int $plantId): JsonResponse
    {
        $locations = Location::where('plant_id', $plantId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($locations);
    }

    /**
     * Store new location (AJAX).
     */
    public function storeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'name' => 'required|string|max:100',
        ]);

        $location = Location::firstOrCreate(
            ['plant_id' => $validated['plant_id'], 'name' => strtoupper($validated['name'])],
        );

        return response()->json([
            'success' => true,
            'data' => $location,
        ]);
    }

    /**
     * Change active location during scanning (AJAX).
     */
    public function changeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        session(['location_id' => $validated['location_id']]);

        return response()->json(['success' => true]);
    }

    /**
     * End current STO session.
     */
    public function endSession(): \Illuminate\Http\RedirectResponse
    {
        $sessionId = session('sto_session_id');
        if ($sessionId) {
            StoSession::where('id', $sessionId)->update(['status' => 'completed']);
            session()->forget(['sto_session_id', 'location_id']);
        }

        return redirect()->route('scan.setup')->with('success', 'Sesi STO berhasil diakhiri.');
    }

    /**
     * Build server-side DataTable response.
     */
    private function buildDatatable($query, Request $request): JsonResponse
    {
        $totalRecords = (clone $query)->count();

        // Search
        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%")
                    ->orWhere('lot', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $filteredRecords = (clone $query)->count();

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $data = $query->skip($start)->take($length)->get();

        // Build response with descending row numbers (Rule 2)
        $rows = $data->map(function ($item, $index) use ($filteredRecords, $start) {
            return [
                'no' => $filteredRecords - $start - $index,
                'id' => $item->id,
                'barcode' => $item->barcode_material,
                'material' => $item->material_name,
                'shape' => $item->shape_name,
                'size' => $item->size,
                'thickness' => $item->thickness,
                'width' => $item->width,
                'diameter' => $item->diameter,
                'length' => $item->length,
                'qty' => $item->qty,
                'lot' => $item->lot ?? '-',
                'user' => $item->user->name ?? '-',
                'plant' => $item->plant->name ?? '-',
                'location' => $item->location->name ?? '-',
                'scan_time' => $item->scan_time->format('H:i:s'),
                'keterangan' => $item->keterangan,
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $rows,
        ]);
    }
}
