<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Plant;
use App\Models\ScanResult;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class RackConfirmationController extends Controller
{
    public function __construct(
        private ActivityLogService $activityLog,
    ) {}

    /**
     * Show the Rack Confirmation index page.
     */
    public function index(): View
    {
        $filterLimit = max((int) config('sto.admin_filter_options_limit', 500), 1);

        return view('admin.rack-confirmation', [
            'plants' => Plant::active()->orderBy('name')->limit($filterLimit)->get(),
        ]);
    }

    /**
     * Datatable endpoint — list all active locations with plant info
     * and distinct barcode count (keterangan=OK, scoped to active STO via Eloquent).
     */
    public function datatable(Request $request): JsonResponse
    {
        // Build the "total barcode per location" subquery using Eloquent
        // so the active_sto global scope is automatically applied.
        $barcodeCountSub = ScanResult::query()
            ->select('location_id')
            ->selectRaw('COUNT(DISTINCT barcode_material) as total_barcode')
            ->where('keterangan', 'OK')
            ->groupBy('location_id');

        $query = Location::active()
            ->with(['plant', 'confirmedBy'])
            ->leftJoinSub($barcodeCountSub, 'barcode_counts', function ($join) {
                $join->on('locations.id', '=', 'barcode_counts.location_id');
            })
            ->select([
                'locations.id',
                'locations.name',
                'locations.plant_id',
                'locations.is_confirmed',
                'locations.confirmed_by_user_id',
                'locations.confirmed_at',
                'locations.confirmation_note',
                DB::raw('COALESCE(barcode_counts.total_barcode, 0) as total_barcode'),
            ]);

        // Filter by plant if provided
        if ($request->filled('filter_plant')) {
            $query->where('locations.plant_id', $request->input('filter_plant'));
        }

        // Filter by status if provided
        if ($request->filled('filter_status')) {
            if ($request->input('filter_status') === 'confirmed') {
                $query->confirmed();
            } elseif ($request->input('filter_status') === 'unconfirmed') {
                $query->unconfirmed();
            }
        }

        return DataTables::of($query)
            ->addColumn('plant_name', fn ($row) => $row->plant?->name ?? '-')
            ->addColumn('total_barcode', fn ($row) => (int) $row->total_barcode)
            ->addColumn('status_badge', function ($row) {
                if ($row->is_confirmed) {
                    return '<span class="badge badge-valid">Terkonfirmasi</span>';
                }
                return '<span class="badge badge-invalid">Belum Dikonfirmasi</span>';
            })
            ->addColumn('confirmed_by_name', fn ($row) => $row->confirmedBy?->name ?? '-')
            ->addColumn('confirmed_at_fmt', fn ($row) => $row->confirmed_at
                ? $row->confirmed_at->format('d/m/Y H:i')
                : '-'
            )
            ->addColumn('confirmation_note_val', fn ($row) => $row->confirmation_note ?? '-')
            ->rawColumns(['status_badge'])
            ->make(true);
    }

    /**
     * Confirm a location (mark as confirmed). Note is optional.
     */
    public function confirm(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($location->is_confirmed) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi ini sudah dikonfirmasi sebelumnya.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldValues = $location->only([
                'is_confirmed',
                'confirmed_by_user_id',
                'confirmed_at',
                'confirmation_note',
            ]);

            $location->update([
                'is_confirmed'         => true,
                'confirmed_by_user_id' => $request->user()->id,
                'confirmed_at'         => now(),
                'confirmation_note'    => $validated['note'] ?? null,
            ]);

            $this->activityLog->record(
                $request->user(),
                'rack_confirmation.confirmed',
                $location,
                oldValues: $oldValues,
                newValues: [
                    'is_confirmed'      => true,
                    'confirmed_by'      => $request->user()->name,
                    'confirmed_at'      => now()->format('Y-m-d H:i:s'),
                    'confirmation_note' => $validated['note'] ?? null,
                ],
            );

            DB::commit();
            Cache::forget('scan_overview:*');
            Cache::forget('sidebar_badges');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('RackConfirmationController@confirm failed', [
                'location_id' => $location->id,
                'exception'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan konfirmasi. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Lokasi \"{$location->name}\" berhasil dikonfirmasi.",
        ]);
    }

    /**
     * Cancel a confirmation. Note/reason is REQUIRED.
     */
    public function cancel(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (! $location->is_confirmed) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi ini belum dikonfirmasi.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Store old confirmation details in log for audit trail
            $oldValues = $location->only([
                'is_confirmed',
                'confirmed_by_user_id',
                'confirmed_at',
                'confirmation_note',
            ]);

            $location->update([
                'is_confirmed'         => false,
                'confirmed_by_user_id' => null,
                'confirmed_at'         => null,
                'confirmation_note'    => $validated['note'],
            ]);

            $this->activityLog->record(
                $request->user(),
                'rack_confirmation.cancelled',
                $location,
                oldValues: $oldValues,
                newValues: [
                    'is_confirmed'      => false,
                    'cancelled_by'      => $request->user()->name,
                    'cancelled_at'      => now()->format('Y-m-d H:i:s'),
                    'cancellation_note' => $validated['note'],
                ],
            );

            DB::commit();
            Cache::forget('scan_overview:*');
            Cache::forget('sidebar_badges');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('RackConfirmationController@cancel failed', [
                'location_id' => $location->id,
                'exception'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan konfirmasi. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Konfirmasi lokasi \"{$location->name}\" berhasil dibatalkan.",
        ]);
    }

    /**
     * Export rack confirmation report to CSV (Excel-compatible).
     */
    public function export(Request $request)
    {
        $barcodeCountSub = ScanResult::query()
            ->select('location_id')
            ->selectRaw('COUNT(DISTINCT barcode_material) as total_barcode')
            ->where('keterangan', 'OK')
            ->groupBy('location_id');

        $query = Location::active()
            ->with(['plant', 'confirmedBy'])
            ->leftJoinSub($barcodeCountSub, 'barcode_counts', function ($join) {
                $join->on('locations.id', '=', 'barcode_counts.location_id');
            })
            ->select([
                'locations.id',
                'locations.name',
                'locations.plant_id',
                'locations.is_confirmed',
                'locations.confirmed_by_user_id',
                'locations.confirmed_at',
                'locations.confirmation_note',
                DB::raw('COALESCE(barcode_counts.total_barcode, 0) as total_barcode'),
            ]);

        if ($request->filled('filter_plant')) {
            $query->where('locations.plant_id', $request->input('filter_plant'));
        }

        if ($request->filled('filter_status')) {
            if ($request->input('filter_status') === 'confirmed') {
                $query->confirmed();
            } elseif ($request->input('filter_status') === 'unconfirmed') {
                $query->unconfirmed();
            }
        }

        $locations = $query->orderBy('locations.plant_id')->orderBy('locations.name')->get();

        $filename = 'Laporan-Konfirmasi-Rak-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($locations) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID Lokasi',
                'Plant',
                'Nama Lokasi / Rak',
                'Status Konfirmasi',
                'Dikonfirmasi Oleh',
                'Waktu Konfirmasi',
                'Catatan Konfirmasi / Batal',
                'Total Barcode Valid (OK)'
            ]);

            foreach ($locations as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->plant?->name ?? '-',
                    $row->name,
                    $row->is_confirmed ? 'Terkonfirmasi' : 'Belum Dikonfirmasi',
                    $row->confirmedBy?->name ?? '-',
                    $row->confirmed_at ? $row->confirmed_at->format('d/m/Y H:i:s') : '-',
                    $row->confirmation_note ?? '-',
                    $row->total_barcode,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
