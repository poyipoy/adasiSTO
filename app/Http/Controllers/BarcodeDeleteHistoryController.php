<?php

namespace App\Http\Controllers;

use App\Models\BarcodeDeleteHistory;
use App\Models\User;
use App\Services\ShapeFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BarcodeDeleteHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $scope = $this->scopedQuery($request->user());

        return view('history.delete-barcode', [
            'plants' => (clone $scope)
                ->whereNotNull('plant_id')
                ->select('plant_id', 'plant_name')
                ->distinct()
                ->orderBy('plant_name')
                ->get(),
            'locations' => (clone $scope)
                ->whereNotNull('location_id')
                ->select('location_id', 'location_name', 'plant_id')
                ->distinct()
                ->orderBy('location_name')
                ->get(),
            'actors' => (clone $scope)
                ->whereNotNull('deleted_by_user_id')
                ->select('deleted_by_user_id', 'deleted_by_name')
                ->distinct()
                ->orderBy('deleted_by_name')
                ->get(),
            'sources' => $this->sourceLabels(),
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $request->validate([
            'plant_id' => ['nullable', 'integer'],
            'location_id' => ['nullable', 'integer'],
            'deleted_by_user_id' => ['nullable', 'integer'],
            'deletion_source' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $scopedQuery = $this->scopedQuery($request->user());
        $recordsTotal = (clone $scopedQuery)->count();
        $query = $this->applyFilters($scopedQuery, $request);

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('barcode_material', 'like', "%{$search}%")
                    ->orWhere('barcode_raw', 'like', "%{$search}%")
                    ->orWhere('material_code', 'like', "%{$search}%")
                    ->orWhere('material_name', 'like', "%{$search}%")
                    ->orWhere('lot_number', 'like', "%{$search}%")
                    ->orWhere('sto_code', 'like', "%{$search}%")
                    ->orWhere('plant_name', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%")
                    ->orWhere('deleted_by_name', 'like', "%{$search}%")
                    ->orWhere('original_user_name', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();
        $maxLength = max((int) config('sto.datatable_max_length', 100), 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), $maxLength);
        $order = $request->input('order.0', []);
        $columns = $request->input('columns', []);
        $column = isset($order['column'], $columns[$order['column']])
            ? ($columns[$order['column']]['data'] ?? null)
            : null;
        $direction = ($order['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortable = [
            'barcode_material' => 'barcode_material',
            'material_name' => 'material_name',
            'original_user_name' => 'original_user_name',
            'plant_name' => 'plant_name',
            'location_name' => 'location_name',
            'deleted_by_name' => 'deleted_by_name',
            'deletion_source' => 'deletion_source',
            'original_scanned_at' => 'original_scanned_at',
            'deleted_at' => 'deleted_at',
        ];

        if ($column && isset($sortable[$column])) {
            $query->orderBy($sortable[$column], $direction);
        } else {
            $query->orderByDesc('deleted_at')->orderByDesc('id');
        }

        $rows = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows->map(fn (BarcodeDeleteHistory $history, int $index) => [
                'no' => $recordsFiltered - $start - $index,
                'id' => $history->id,
                'barcode_material' => $history->barcode_material ?? '-',
                'material_name' => $history->material_name ?? '-',
                'material_code' => $history->material_code ?? '-',
                'shape_name' => ShapeFormatter::name($history->shape_code, $history->shape_name),
                'size' => $this->size($history),
                'original_user_name' => $history->original_user_name ?? '-',
                'plant_name' => $history->plant_name ?? '-',
                'location_name' => $history->location_name ?? '-',
                'deleted_by_name' => $history->deleted_by_name ?? '-',
                'deletion_source' => $this->sourceLabels()[$history->deletion_source] ?? $history->deletion_source,
                'original_scanned_at' => $history->original_scanned_at?->format('Y-m-d H:i:s') ?? '-',
                'deleted_at' => $history->deleted_at?->format('Y-m-d H:i:s') ?? '-',
            ])->values(),
        ]);
    }

    public function show(Request $request, BarcodeDeleteHistory $history): JsonResponse
    {
        abort_unless(
            $this->scopedQuery($request->user())->whereKey($history->id)->exists(),
            403,
            'Anda tidak memiliki akses ke history delete ini.',
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $history->id,
                'original_scan_result_id' => $history->original_scan_result_id,
                'sto_code' => $history->sto_code,
                'barcode_raw' => $history->barcode_raw,
                'barcode_material' => $history->barcode_material,
                'material_code' => $history->material_code,
                'material_name' => $history->material_name,
                'shape_code' => $history->shape_code,
                'shape_name' => ShapeFormatter::name($history->shape_code, $history->shape_name),
                'size' => $this->size($history),
                'lot_number' => $history->lot_number,
                'qty' => $history->qty,
                'keterangan' => $history->keterangan,
                'scan_source' => $history->scan_source,
                'original_user_name' => $history->original_user_name,
                'plant_name' => $history->plant_name,
                'location_name' => $history->location_name,
                'deleted_by_name' => $history->deleted_by_name,
                'deletion_source' => $this->sourceLabels()[$history->deletion_source] ?? $history->deletion_source,
                'deletion_reason' => $history->deletion_reason,
                'original_scanned_at' => $history->original_scanned_at?->format('Y-m-d H:i:s'),
                'deleted_at' => $history->deleted_at?->format('Y-m-d H:i:s'),
                'snapshot' => $history->snapshot,
            ],
        ]);
    }

    private function scopedQuery(User $user): Builder
    {
        return BarcodeDeleteHistory::query()
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where(function (Builder $scope) use ($user) {
                $scope->where('deleted_by_user_id', $user->id)
                    ->orWhere('original_user_id', $user->id);
            }));
    }

    private function applyFilters(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->filled('plant_id'), fn (Builder $builder) => $builder->where('plant_id', $request->integer('plant_id')))
            ->when($request->filled('location_id'), fn (Builder $builder) => $builder->where('location_id', $request->integer('location_id')))
            ->when($request->filled('deleted_by_user_id'), fn (Builder $builder) => $builder->where('deleted_by_user_id', $request->integer('deleted_by_user_id')))
            ->when($request->filled('deletion_source'), fn (Builder $builder) => $builder->where('deletion_source', $request->string('deletion_source')->toString()))
            ->when($request->filled('date_from'), fn (Builder $builder) => $builder->where('deleted_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay()))
            ->when($request->filled('date_to'), fn (Builder $builder) => $builder->where('deleted_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay()));
    }

    private function size(BarcodeDeleteHistory $history): string
    {
        if ($history->shape_code === 'RR') {
            return "\u{2300}{$history->diameter} x {$history->length}";
        }

        return "{$history->thickness} x {$history->width} x {$history->length}";
    }

    private function sourceLabels(): array
    {
        return [
            'scan_history' => 'Scan History',
            'admin_scan_results' => 'All Scan Results',
            'material_double' => 'Material Double',
        ];
    }
}
