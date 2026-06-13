<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ScanResultsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpsertScanResultRequest;
use App\Models\Location;
use App\Models\MasterKeterangan;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Models\User;
use App\Services\ExportService;
use App\Services\ScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class DashboardController extends Controller
{
    public function __construct(
        private ScanService $scanService,
        private ExportService $exportService,
    ) {}

    public function index(): View
    {
        $totalScanToday = ScanResult::today()->count();
        $totalScanMonth = ScanResult::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $totalValid = ScanResult::where('keterangan', 'OK')->count();
        $totalInvalid = ScanResult::where('keterangan', '!=', 'OK')->count();
        $totalDuplicate = ScanResult::query()
            ->select('sto_code', 'barcode_material')
            ->groupBy('sto_code', 'barcode_material')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        $scanPerUser = ScanResult::selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('user:id,name')
            ->get()
            ->map(fn ($item) => ['name' => $item->user->name ?? 'Unknown', 'total' => $item->total]);

        $scanPerPlant = ScanResult::selectRaw('plant_id, COUNT(*) as total')
            ->groupBy('plant_id')
            ->with('plant:id,name')
            ->get()
            ->map(fn ($item) => ['name' => $item->plant->name ?? 'Unknown', 'total' => $item->total]);

        $scanPerDay = ScanResult::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => ['date' => $item->date, 'total' => $item->total]);

        $topMaterial = ScanResult::selectRaw('material_name, COUNT(*) as total')
            ->groupBy('material_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $latestScan = ScanResult::with(['user', 'plant', 'location'])
            ->latestFirst()
            ->paginate(50, ['*'], 'latest_page')
            ->withQueryString();

        return view('admin.dashboard', compact(
            'totalScanToday',
            'totalScanMonth',
            'totalValid',
            'totalDuplicate',
            'totalInvalid',
            'scanPerUser',
            'scanPerPlant',
            'scanPerDay',
            'topMaterial',
            'latestScan'
        ));
    }

    public function scanResults(): View
    {
        return view('admin.scan-results', [
            'plants' => Plant::active()->orderBy('name')->get(),
            'locations' => Location::active()->with(['user', 'plant'])->orderBy('name')->get(),
            'users' => User::where('role', 'scanner')->where('is_active', true)->orderBy('name')->get(),
            'materials' => MasterMaterial::active()->orderBy('material_code')->get(),
            'keteranganList' => MasterKeterangan::active()->orderBy('name')->pluck('name'),
            'stoCodes' => StoCode::orderByDesc('is_active')->orderByDesc('created_at')->pluck('code'),
            'stoOptions' => StoCode::orderByDesc('is_active')->orderByDesc('created_at')->get(['id', 'code']),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $baseQuery = $this->exportService->filteredScanResults($request->all());
        $totalRecords = ScanResult::count();

        $search = $request->input('search.value');
        if ($search) {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('barcode_raw', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%")
                    ->orWhere('sto_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $filteredRecords = (clone $baseQuery)->count();
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $data = $baseQuery->skip($start)->take($length)->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data->map(function (ScanResult $scanResult, int $index) use ($filteredRecords, $start) {
                $row = $this->scanService->serializeScan($scanResult);

                return array_merge($row, [
                    'no' => $filteredRecords - $start - $index,
                    'user' => $scanResult->user->name ?? '-',
                    'user_id' => $scanResult->user_id,
                    'sto_code_id' => $scanResult->sto_code_id,
                    'plant_id' => $scanResult->plant_id,
                    'location_id' => $scanResult->location_id,
                    'location_name' => $scanResult->location->name ?? '-',
                ]);
            })->values(),
        ]);
    }

    public function store(AdminUpsertScanResultRequest $request): JsonResponse
    {
        $result = $this->scanService->storeByAdmin($request->user(), $request->validated());

        if (!$result['success']) {
            return response()->json(
                collect($result)->except('status')->all(),
                $result['status'] ?? 422
            );
        }

        return response()->json($result, 201);
    }

    public function update(AdminUpsertScanResultRequest $request, int $id): JsonResponse
    {
        $this->scanService->updateByAdmin($request->user(), $id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data scan berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->scanService->deleteByAdmin($request->user(), $id);

        return response()->json([
            'success' => true,
            'message' => 'Data scan berhasil dihapus.',
        ]);
    }

    public function materialSummary(): View
    {
        return view('admin.barcode-overview', [
            'plants' => Plant::active()->orderBy('name')->get(),
            'stoCodes' => StoCode::orderByDesc('is_active')->orderByDesc('created_at')->pluck('code'),
        ]);
    }

    public function materialSummaryData(Request $request): JsonResponse
    {
        $query = ScanResult::query()
            ->selectRaw('barcode_material, material_code, material_name, shape_code, shape_name, thickness, width, diameter, length, SUM(qty) as qty_total, COUNT(*) as scan_count')
            ->groupBy('barcode_material', 'material_code', 'material_name', 'shape_code', 'shape_name', 'thickness', 'width', 'diameter', 'length');

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->plant_id);
        }

        if ($request->filled('sto_code')) {
            $query->where('sto_code', $request->sto_code);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%");
            });
        }

        $filteredRecords = (clone $query)->get()->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $data = $query->orderByDesc('scan_count')->skip($start)->take($length)->get();

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

    public function exportExcel(Request $request)
    {
        $filters = $this->exportService->exportFilters($request->all());
        $filename = 'STO_Scan_Results_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ScanResultsExport($filters), $filename);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->exportService->exportFilters($request->all());
        $rows = $this->exportService->filteredScanResults($filters)->limit(5000)->get();
        $filename = 'STO_Scan_Results_' . now()->format('Ymd_His') . '.pdf';

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.scan-results-pdf', compact('rows', 'filters'))
                ->setPaper('a4', 'landscape');

            return $pdf->download($filename);
        }

        return response()
            ->view('exports.scan-results-pdf', compact('rows', 'filters'))
            ->header('Content-Type', 'text/html');
    }
}
