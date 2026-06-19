<?php

namespace App\Services;

use App\Models\MaterialDoubleValidation;
use App\Models\ScanResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OverviewService
{
    public function __construct(private MaterialDoubleQueryService $materialDoubleQuery) {}

    public function scanOverview(?User $scopeUser = null, array $filters = []): array
    {
        $baseQuery = $this->scanQuery($scopeUser, $filters);

        return [
            'total_today' => (clone $baseQuery)
                ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
                ->count(),
            'valid_scans' => (clone $baseQuery)->where('keterangan', 'OK')->count(),
            'duplicate_scans' => $this->duplicateGroupCount(clone $baseQuery),
            'invalid_scans' => (clone $baseQuery)->where('keterangan', '!=', 'OK')->count(),
        ];
    }

    public function activityOverview(?User $scopeUser = null, array $filters = []): Collection
    {
        return $this->scanQuery($scopeUser, $filters)
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->with('user:id,name')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->user->name ?? 'Unknown',
                'total' => (int) $item->total,
            ]);
    }

    public function scanPerDay(?User $scopeUser = null, array $filters = [], int $days = 7): Collection
    {
        return $this->scanQuery($scopeUser, $filters)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'total' => (int) $item->total,
            ]);
    }

    public function validatorOverview(array $filters = []): array
    {
        unset($filters['status']);

        $groups = $this->materialDoubleQuery->duplicateGroupQuery($filters);

        return [
            'total_barcode' => DB::query()
                ->fromSub(clone $groups, 'validator_total_barcode')
                ->count(),
            'valid' => DB::query()
                ->fromSub(clone $groups, 'validator_valid_barcode')
                ->whereNotNull('validated_at')
                ->count(),
            'need_check' => DB::query()
                ->fromSub(clone $groups, 'validator_need_check_barcode')
                ->whereNull('validated_at')
                ->count(),
        ];
    }

    public function validationByScanner(array $filters = []): Collection
    {
        unset($filters['status']);

        $groups = $this->materialDoubleQuery->duplicateGroupQuery($filters);

        $query = MaterialDoubleValidation::query()
            ->joinSub($groups, 'material_double_groups', function ($join) {
                $join->on('material_double_groups.barcode_material', '=', 'material_double_validations.barcode_material')
                    ->on('material_double_groups.plant_id', '=', 'material_double_validations.plant_id')
                    ->on('material_double_groups.location_id', '=', 'material_double_validations.location_id');
            })
            ->join('users', 'material_double_validations.validated_by', '=', 'users.id')
            ->where(function ($query) {
                $query->where('users.role', 'admin')
                    ->orWhere(function ($scannerQuery) {
                        $scannerQuery->where('users.role', 'scanner')
                            ->where('users.is_validator', true);
                    });
            })
            ->selectRaw('users.id, users.name, COUNT(*) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total');

        return $query->get()->map(fn ($item) => [
            'name' => $item->name,
            'total' => (int) $item->total,
        ]);
    }

    public function materialSummaryQuery(?User $scopeUser = null, array $filters = []): Builder
    {
        $query = $this->scanQuery($scopeUser, $filters)
            ->selectRaw('barcode_material, material_code, material_name, shape_code, shape_name, thickness, width, diameter, length, SUM(qty) as qty_total, COUNT(*) as scan_count')
            ->groupBy('barcode_material', 'material_code', 'material_name', 'shape_code', 'shape_name', 'thickness', 'width', 'diameter', 'length');

        if (!empty($filters['material_code'])) {
            $searchMatCode = str_replace(['%', '_'], ['\\%', '\\_'], $filters['material_code']);
            $query->where('material_code', 'like', '%' . $searchMatCode . '%');
        }

        if (!empty($filters['material_name'])) {
            $searchMatName = str_replace(['%', '_'], ['\\%', '\\_'], $filters['material_name']);
            $query->where('material_name', 'like', '%' . $searchMatName . '%');
        }

        if (!empty($filters['shape_code'])) {
            $query->where('shape_code', $filters['shape_code']);
        }

        return $query;
    }

    public function scanQuery(?User $scopeUser = null, array $filters = []): Builder
    {
        $query = ScanResult::query();

        if ($scopeUser) {
            $query->where('user_id', $scopeUser->id);
        }

        if (!empty($filters['plant_id'])) {
            $query->where('plant_id', $filters['plant_id']);
        }

        if (!empty($filters['sto_code'])) {
            $query->where('sto_code', $filters['sto_code']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query;
    }

    private function duplicateGroupCount(Builder $query): int
    {
        $groups = $query
            ->select('barcode_material')
            ->groupBy('barcode_material')
            ->havingRaw('COUNT(*) > 1');

        return DB::query()->fromSub($groups, 'duplicate_groups')->count();
    }

    private function applyScanFilters($query, array $filters, string $prefix = ''): void
    {
        if (!empty($filters['plant_id'])) {
            $query->where($prefix . 'plant_id', $filters['plant_id']);
        }

        if (!empty($filters['sto_code'])) {
            $query->where($prefix . 'sto_code', $filters['sto_code']);
        }

        if (!empty($filters['date_from'])) {
            $query->where($prefix . 'created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where($prefix . 'created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
    }
}
