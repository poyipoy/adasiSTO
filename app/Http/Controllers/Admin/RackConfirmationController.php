<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Plant;
use App\Models\RackConfirmation;
use App\Models\ScanResult;
use App\Services\ActiveStoService;
use App\Services\RackConfirmationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class RackConfirmationController extends Controller
{
    public function __construct(
        private ActiveStoService $activeStoService,
        private RackConfirmationService $rackConfirmationService,
    ) {}

    public function index(): View
    {
        return view('admin.rack-confirmation', [
            'plants' => Plant::active()->orderBy('name')->get(),
            'activeSto' => $this->activeStoService->active(),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = $this->baseQuery($request);
        $user = $request->user();

        return DataTables::of($query)
            ->addColumn('plant_name', fn ($row) => $row->plant_name ?? '-')
            ->editColumn('total_barcode', fn ($row) => (int) $row->total_barcode)
            ->addColumn('status_badge', fn ($row) => $this->statusBadge($row->rack_status))
            ->addColumn('actor_name', fn ($row) => match ($row->rack_status) {
                RackConfirmation::STATUS_FINISHED => $row->finished_by_name ?? '-',
                RackConfirmation::STATUS_VALIDATED => $row->validated_by_name ?? '-',
                RackConfirmation::STATUS_CONFIRMED => $row->confirmed_by_name ?? '-',
                default => '-',
            })
            ->addColumn('action_at', fn ($row) => match ($row->rack_status) {
                RackConfirmation::STATUS_FINISHED => $row->finished_at,
                RackConfirmation::STATUS_VALIDATED => $row->validated_at,
                RackConfirmation::STATUS_CONFIRMED => $row->confirmed_at,
                default => null,
            })
            ->addColumn('allowed_actions', fn ($row) => $this->rackConfirmationService->allowedActions($user, $row->rack_status))
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    public function finish(Request $request, Location $location): JsonResponse
    {
        $payload = $request->validate(['note' => ['nullable', 'string', 'max:500']]);
        $this->rackConfirmationService->transition($request->user(), $location, 'finish', $payload['note'] ?? null);

        return response()->json(['success' => true, 'message' => "Rack {$location->name} ditandai Finished."]);
    }

    public function validateRack(Request $request, Location $location): JsonResponse
    {
        $payload = $request->validate(['note' => ['nullable', 'string', 'max:500']]);
        $this->rackConfirmationService->transition($request->user(), $location, 'validate', $payload['note'] ?? null);

        return response()->json(['success' => true, 'message' => "Rack {$location->name} berhasil divalidasi."]);
    }

    public function confirm(Request $request, Location $location): JsonResponse
    {
        $payload = $request->validate(['note' => ['nullable', 'string', 'max:500']]);
        $this->rackConfirmationService->transition($request->user(), $location, 'confirm', $payload['note'] ?? null);

        return response()->json(['success' => true, 'message' => "Rack {$location->name} berhasil dikonfirmasi."]);
    }

    public function cancel(Request $request, Location $location): JsonResponse
    {
        $payload = $request->validate(['note' => ['required', 'string', 'min:5', 'max:500']]);
        $this->rackConfirmationService->transition($request->user(), $location, 'cancel', $payload['note']);

        return response()->json(['success' => true, 'message' => "Workflow Rack {$location->name} dikembalikan ke Pending."]);
    }

    public function export(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);
        $rows = $this->baseQuery($request)
            ->orderBy('plant_name')
            ->orderBy('locations.name')
            ->get();
        $filename = 'Laporan-Konfirmasi-Rak-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'ID Lokasi', 'Plant', 'Location', 'Status', 'Finished By', 'Finished At',
                'Validated By', 'Validated At', 'Confirmed By', 'Confirmed At', 'Note', 'Total Scan Valid (OK)',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id, $row->plant_name ?? '-', $row->name, strtoupper($row->rack_status),
                    $row->finished_by_name ?? '-', $row->finished_at ?? '-',
                    $row->validated_by_name ?? '-', $row->validated_at ?? '-',
                    $row->confirmed_by_name ?? '-', $row->confirmed_at ?? '-',
                    $row->note ?? '-', (int) $row->total_barcode,
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function baseQuery(Request $request): Builder
    {
        $activeStoId = $this->activeStoService->active()?->id ?? 0;
        $barcodeCountSub = ScanResult::withoutGlobalScope('active_sto')
            ->select('location_id')
            ->selectRaw('COUNT(*) as total_barcode')
            ->where('sto_code_id', $activeStoId)
            ->where('keterangan', 'OK')
            ->groupBy('location_id');

        $query = DB::table('locations')
            ->leftJoin('plants', 'plants.id', '=', 'locations.plant_id')
            ->leftJoinSub($barcodeCountSub, 'barcode_counts', 'locations.id', '=', 'barcode_counts.location_id')
            ->leftJoin('rack_confirmations as rc', function ($join) use ($activeStoId) {
                $join->on('rc.location_id', '=', 'locations.id')->where('rc.sto_code_id', $activeStoId);
            })
            ->leftJoin('users as finished_user', 'finished_user.id', '=', 'rc.finished_by_user_id')
            ->leftJoin('users as validated_user', 'validated_user.id', '=', 'rc.validated_by_user_id')
            ->leftJoin('users as confirmed_user', 'confirmed_user.id', '=', 'rc.confirmed_by_user_id')
            ->where('locations.is_active', true)
            ->select([
                'locations.id', 'locations.name', 'locations.plant_id', 'plants.name as plant_name',
                DB::raw("COALESCE(rc.status, 'pending') as rack_status"),
                'rc.note', 'rc.finished_at', 'rc.validated_at', 'rc.confirmed_at',
                'finished_user.name as finished_by_name',
                'validated_user.name as validated_by_name',
                'confirmed_user.name as confirmed_by_name',
                DB::raw('COALESCE(barcode_counts.total_barcode, 0) as total_barcode'),
            ]);

        if ($request->filled('filter_plant')) {
            $query->where('locations.plant_id', $request->input('filter_plant'));
        }
        if ($request->filled('filter_status')) {
            $query->whereRaw("COALESCE(rc.status, 'pending') = ?", [$request->input('filter_status')]);
        }

        return $query;
    }

    private function statusBadge(string $status): string
    {
        $class = match ($status) {
            RackConfirmation::STATUS_CONFIRMED => 'badge-valid',
            RackConfirmation::STATUS_VALIDATED => 'badge-info',
            RackConfirmation::STATUS_FINISHED => 'badge-warning',
            default => 'badge-invalid',
        };

        return '<span class="badge '.$class.'">'.strtoupper($status).'</span>';
    }
}
