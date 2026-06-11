<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\ScanResultLog;
use App\Models\StoSession;
use App\Models\User;
use App\Models\MasterKeterangan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ScanResultsExport;

class DashboardController extends Controller
{
    /**
     * Admin dashboard with statistics.
     */
    public function index(): View
    {
        $totalScanAll = ScanResult::count();
        $totalScanToday = ScanResult::today()->count();
        $totalUsers = User::where('role', 'user')->where('is_active', true)->count();
        $totalPlants = Plant::active()->count();

        // Scan per user (top 10)
        $scanPerUser = ScanResult::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('user:id,name')
            ->get()
            ->map(fn($item) => [
                'name' => $item->user->name ?? 'Unknown',
                'total' => $item->total,
            ]);

        // Scan per plant
        $scanPerPlant = ScanResult::selectRaw('plant_id, COUNT(*) as total')
            ->groupBy('plant_id')
            ->with('plant:id,name')
            ->get()
            ->map(fn($item) => [
                'name' => $item->plant->name ?? 'Unknown',
                'total' => $item->total,
            ]);

        // Scan trend last 7 days
        $scanTrend = ScanResult::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' => $item->date,
                'total' => $item->total,
            ]);

        return view('admin.dashboard', compact(
            'totalScanAll',
            'totalScanToday',
            'totalUsers',
            'totalPlants',
            'scanPerUser',
            'scanPerPlant',
            'scanTrend'
        ));
    }

    /**
     * Scan results monitoring page.
     */
    public function scanResults(): View
    {
        $plants = Plant::active()->get();
        $users = User::where('role', 'user')->where('is_active', true)->get();
        $keteranganList = MasterKeterangan::active()->pluck('name');
        $stoCodes = StoSession::distinct()->pluck('sto_code');

        return view('admin.scan-results', compact('plants', 'users', 'keteranganList', 'stoCodes'));
    }

    /**
     * Server-side DataTable for admin (all data).
     */
    public function datatable(Request $request): JsonResponse
    {
        $query = ScanResult::with(['user', 'plant', 'location', 'stoSession'])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('sto_code')) {
            $query->whereHas('stoSession', fn($q) => $q->where('sto_code', $request->sto_code));
        }
        if ($request->filled('keterangan')) {
            $query->where('keterangan', $request->keterangan);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $totalRecords = ScanResult::count();

        // Search
        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%")
                    ->orWhere('lot', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        $filteredRecords = $query->count();

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $data = $query->skip($start)->take($length)->get();

        $rows = $data->map(function ($item, $index) use ($filteredRecords, $start) {
            return [
                'no' => $filteredRecords - $start - $index,
                'id' => $item->id,
                'barcode' => $item->barcode_material,
                'material' => $item->material_name,
                'shape' => $item->shape_name,
                'thickness' => $item->thickness,
                'width' => $item->width,
                'diameter' => $item->diameter,
                'length' => $item->length,
                'qty' => $item->qty,
                'lot' => $item->lot ?? '-',
                'user' => $item->user->name ?? '-',
                'plant' => $item->plant->name ?? '-',
                'location' => $item->location->name ?? '-',
                'sto_code' => $item->stoSession->sto_code ?? '-',
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

    /**
     * Edit scan result.
     */
    public function edit(int $id): JsonResponse
    {
        $scanResult = ScanResult::with(['location', 'plant', 'user', 'stoSession'])->findOrFail($id);
        $locations = \App\Models\Location::where('plant_id', $scanResult->plant_id)->get();
        $keteranganList = \App\Models\MasterKeterangan::active()->pluck('name');
        $plants = \App\Models\Plant::active()->get();
        $users = \App\Models\User::where('role', 'user')->where('is_active', true)->get();

        return response()->json([
            'data' => $scanResult,
            'locations' => $locations,
            'keterangan_list' => $keteranganList,
            'plants' => $plants,
            'users' => $users,
        ]);
    }

    /**
     * Update scan result (admin).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'barcode_material' => 'required|string|max:100',
            'material_name' => 'nullable|string|max:100',
            'shape_name' => 'nullable|string|max:100',
            'thickness' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'diameter' => 'nullable|numeric',
            'length' => 'nullable|numeric',
            'lot' => 'nullable|string|max:100',
            'qty' => 'required|integer|min:1',
            'keterangan' => 'required|string|max:100',
            'location_id' => 'required|exists:locations,id',
            'plant_id' => 'required|exists:plants,id',
            'user_id' => 'required|exists:users,id',
            'scan_time' => 'required|date',
        ]);

        $scanResult = ScanResult::findOrFail($id);
        $oldValues = $scanResult->only([
            'barcode_material', 'material_name', 'shape_name', 
            'thickness', 'width', 'diameter', 'length', 
            'lot', 'qty', 'keterangan', 'location_id', 'plant_id', 'user_id', 'scan_time'
        ]);

        $scanResult->update($validated);

        ScanResultLog::create([
            'scan_result_id' => $scanResult->id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $validated,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate!',
        ]);
    }

    /**
     * Delete scan result (admin).
     */
    public function destroy(int $id): JsonResponse
    {
        $scanResult = ScanResult::findOrFail($id);

        ScanResultLog::create([
            'scan_result_id' => $scanResult->id,
            'user_id' => auth()->id(),
            'action' => 'deleted',
            'old_values' => $scanResult->toArray(),
        ]);

        $scanResult->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil dihapus!',
        ]);
    }

    /**
     * Export scan results to Excel.
     */
    public function export(Request $request)
    {
        $filename = 'STO_Export_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new ScanResultsExport($request), $filename);
    }

    /**
     * Barcode overview page — group same barcodes.
     */
    public function barcodeOverview(): View
    {
        $plants = Plant::active()->get();
        $stoCodes = StoSession::distinct()->pluck('sto_code');

        return view('admin.barcode-overview', compact('plants', 'stoCodes'));
    }

    /**
     * Server-side DataTable for barcode overview (grouped).
     */
    public function overviewDatatable(Request $request): JsonResponse
    {
        $query = ScanResult::selectRaw(
            'barcode_material, material_code, material_name, shape_code, shape_name, '
            . 'thickness, width, diameter, length, '
            . 'SUM(qty) as qty_total, COUNT(*) as scan_count'
        )
            ->groupBy('barcode_material', 'material_code', 'material_name', 'shape_code', 'shape_name', 'thickness', 'width', 'diameter', 'length');

        // Filters
        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }
        if ($request->filled('sto_code')) {
            $query->whereHas('stoSession', fn($q) => $q->where('sto_code', $request->sto_code));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $totalRecords = (clone $query)->get()->count();

        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%");
            });
        }

        $filteredRecords = (clone $query)->get()->count();

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $data = $query->orderByDesc('scan_count')
            ->skip($start)
            ->take($length)
            ->get();

        $rows = $data->map(function ($item, $index) use ($filteredRecords, $start) {
            $size = '-';
            if ($item->shape_code === 'RF') {
                $size = "{$item->thickness} x {$item->width} x {$item->length}";
            } elseif ($item->shape_code === 'RR') {
                $size = "Ø{$item->diameter} x {$item->length}";
            }

            return [
                'no' => $filteredRecords - $start - $index,
                'barcode' => $item->barcode_material,
                'material' => $item->material_name,
                'shape' => $item->shape_name,
                'size' => $size,
                'qty_total' => $item->qty_total,
                'scan_count' => $item->scan_count,
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
