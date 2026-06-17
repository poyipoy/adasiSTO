<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ScanResultsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpsertScanResultRequest;
use App\Models\ExportRequest;
use App\Models\Location;
use App\Models\MasterKeterangan;
use App\Models\MasterMaterial;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Models\StoCode;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ExportService;
use App\Services\ScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class DashboardController extends Controller
{
    public function __construct(
        private ScanService $scanService,
        private ExportService $exportService,
        private ActivityLogService $activityLog,
    ) {}

    public function index(Request $request): View
    {
        $baseQuery = $this->dashboardScanQuery($request);

        $totalScanToday = (clone $baseQuery)->today()->count();
        $totalScanMonth = (clone $baseQuery)->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();
        $totalValid = (clone $baseQuery)->where('keterangan', 'OK')->count();
        $totalInvalid = (clone $baseQuery)->where('keterangan', '!=', 'OK')->count();
        
        $duplicateQuery = (clone $baseQuery)
            ->select('sto_code', 'barcode_material')
            ->groupBy('sto_code', 'barcode_material')
            ->havingRaw('COUNT(*) > 1');
        $totalDuplicate = DB::query()->fromSub($duplicateQuery, 'duplicate_scan_groups')->count();

        $scanPerUser = (clone $baseQuery)->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('user:id,name')
            ->get()
            ->map(fn ($item) => ['name' => $item->user->name ?? 'Unknown', 'total' => $item->total]);

        // Always show all plants in the pill navigation, don't use base query
        $scanPerPlant = ScanResult::selectRaw('plant_id, COUNT(*) as total')
            ->groupBy('plant_id')
            ->with('plant:id,name')
            ->get()
            ->map(fn ($item) => ['id' => $item->plant_id, 'name' => $item->plant->name ?? 'Unknown', 'total' => $item->total]);

        $scanPerDay = (clone $baseQuery)->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => ['date' => $item->date, 'total' => $item->total]);

        return view('admin.dashboard', compact(
            'totalScanToday',
            'totalScanMonth',
            'totalValid',
            'totalDuplicate',
            'totalInvalid',
            'scanPerUser',
            'scanPerPlant',
            'scanPerDay'
        ));
    }

    public function latestScanData(Request $request): JsonResponse
    {
        $baseQuery = $this->dashboardScanQuery($request)
            ->with([
                'user:id,name',
                'plant:id,name',
                'location:id,name',
            ])
            ->select([
                'id',
                'user_id',
                'plant_id',
                'location_id',
                'sto_code',
                'barcode_material',
                'material_code',
                'material_name',
                'lot_number',
                'created_at',
            ]);

        $totalRecords = (clone $baseQuery)->count();

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%")
                    ->orWhere('sto_code', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('plant', fn ($plantQuery) => $plantQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('location', fn ($locationQuery) => $locationQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $filteredRecords = (clone $baseQuery)->count();
        $maxLength = max((int) config('sto.dashboard_latest_max_length', 50), 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', $maxLength), 1), $maxLength);

        $data = $baseQuery
            ->latestFirst()
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data->map(fn (ScanResult $scanResult) => [
                'barcode_material' => $scanResult->barcode_material,
                'material_name' => $scanResult->material_name,
                'material_code' => $scanResult->material_code,
                'lot_number' => $scanResult->lot_number,
                'user' => $scanResult->user->name ?? '-',
                'plant' => $scanResult->plant->name ?? '-',
                'location_name' => $scanResult->location->name ?? '-',
                'sto_code' => $scanResult->sto_code,
                'created_at' => $scanResult->created_at?->format('Y-m-d H:i:s'),
            ])->values(),
        ]);
    }

    public function scanResults(): View
    {
        $filterLimit = max((int) config('sto.admin_filter_options_limit', 500), 1);

        return view('admin.scan-results', [
            'plants' => Plant::active()->orderBy('name')->limit($filterLimit)->get(),
            'locations' => Location::active()->with(['user', 'plant'])->orderBy('name')->limit($filterLimit)->get(),
            'users' => User::where('role', 'scanner')->where('is_active', true)->orderBy('name')->limit($filterLimit)->get(),
            'materials' => MasterMaterial::active()->orderBy('material_code')->limit($filterLimit)->get(),
            'keteranganList' => MasterKeterangan::active()->orderBy('name')->pluck('name'),
            'stoCodes' => StoCode::orderByDesc('is_active')->orderByDesc('created_at')->limit($filterLimit)->pluck('code'),
            'stoOptions' => StoCode::orderByDesc('is_active')->orderByDesc('created_at')->limit($filterLimit)->get(['id', 'code']),
        ]);
    }

    private function dashboardScanQuery(Request $request)
    {
        $query = ScanResult::query();

        if ($request->filled('plant_id')) {
            $query->where('plant_id', $request->input('plant_id'));
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from') . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        return $query;
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
                'material_name' => 'material_name',
                'shape_name' => 'shape_name',
                'thickness' => 'thickness',
                'width' => 'width',
                'diameter' => 'diameter',
                'length' => 'length',
                'lot_number' => 'lot_number',
                'qty' => 'qty',
                'created_at' => 'scan_results.created_at',
                'keterangan' => 'keterangan',
            ];

            if (isset($sortableColumns[$columnData])) {
                $baseQuery->reorder($sortableColumns[$columnData], $dir);
            } elseif ($columnData === 'user') {
                $baseQuery->join('users', 'scan_results.user_id', '=', 'users.id')
                    ->select('scan_results.*')
                    ->reorder('users.name', $dir);
            } elseif ($columnData === 'plant') {
                $baseQuery->join('plants', 'scan_results.plant_id', '=', 'plants.id')
                    ->select('scan_results.*')
                    ->reorder('plants.name', $dir);
            } elseif ($columnData === 'location_name') {
                $baseQuery->join('locations', 'scan_results.location_id', '=', 'locations.id')
                    ->select('scan_results.*')
                    ->reorder('locations.name', $dir);
            }
        }

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

        if ($request->filled('material_code')) {
            $query->where('material_code', 'like', '%' . $request->material_code . '%');
        }

        if ($request->filled('material_name')) {
            $query->where('material_name', 'like', '%' . $request->material_name . '%');
        }

        if ($request->filled('shape_code')) {
            $query->where('shape_code', $request->shape_code);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $search = $request->input('search.value');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%");
            });
        }

        $filteredRecords = DB::query()
            ->fromSub((clone $query), 'material_summary_count')
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
            ];

            if (isset($sortableColumns[$columnData])) {
                $query->orderBy($sortableColumns[$columnData], $dir);
            } elseif ($columnData === 'size') {
                $query->orderBy('thickness', $dir)->orderBy('diameter', $dir);
            } else {
                $query->orderByDesc('scan_count');
            }
        } else {
            $query->orderByDesc('scan_count');
        }

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

        $this->activityLog->record($request->user(), 'export.scan_results.requested', metadata: [
            'format' => 'excel',
            'filters' => $filters,
        ]);

        try {
            return Excel::download(new ScanResultsExport($filters), $filename);
        } catch (Throwable $exception) {
            Log::error('Scan export failed', [
                'user_id' => $request->user()?->id,
                'format' => 'excel',
                'exception' => $exception::class,
            ]);

            $this->activityLog->record($request->user(), 'export.scan_results.failed', metadata: [
                'format' => 'excel',
                'filters' => $filters,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }

    public function queueExport(Request $request, string $format): JsonResponse
    {
        try {
            $exportRequest = $this->exportService->queueScanResultsExport($request->user(), $format, $request->all());

            $this->activityLog->record($request->user(), 'export.scan_results.requested', $exportRequest, metadata: [
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
            Log::error('Async scan export queue failed', [
                'user_id' => $request->user()?->id,
                'format' => $format,
                'exception' => $exception::class,
            ]);

            $this->activityLog->record($request->user(), 'export.scan_results.failed', metadata: [
                'format' => $format,
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
            ->recentScanResultExports($request->user())
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

        return Storage::disk($exportRequest->file_disk)->download(
            $exportRequest->file_path,
            $exportRequest->file_name,
            ['Content-Type' => $exportRequest->mime_type ?: 'application/octet-stream'],
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->exportService->exportFilters($request->all());
        $filename = 'STO_Scan_Results_' . now()->format('Ymd_His') . '.pdf';

        $this->activityLog->record($request->user(), 'export.scan_results.requested', metadata: [
            'format' => 'pdf',
            'filters' => $filters,
        ]);

        try {
            $rows = $this->exportService->filteredScanResults($filters)
                ->limit((int) config('sto.export_pdf_row_limit', 5000))
                ->get();

            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.scan-results-pdf', compact('rows', 'filters'))
                    ->setPaper('a4', 'landscape');

                return $pdf->download($filename);
            }

            return response()
                ->view('exports.scan-results-pdf', compact('rows', 'filters'))
                ->header('Content-Type', 'text/html');
        } catch (Throwable $exception) {
            Log::error('Scan export failed', [
                'user_id' => $request->user()?->id,
                'format' => 'pdf',
                'exception' => $exception::class,
            ]);

            $this->activityLog->record($request->user(), 'export.scan_results.failed', metadata: [
                'format' => 'pdf',
                'filters' => $filters,
                'exception' => $exception::class,
            ]);

            throw $exception;
        }
    }
}
