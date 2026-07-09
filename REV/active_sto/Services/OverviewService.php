<?php

namespace App\Services;

use App\Models\MaterialDoubleValidation;
use App\Models\ScanResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OverviewService
{
    public function __construct(private MaterialDoubleQueryService $materialDoubleQuery) {}

    public function scanOverview(?User $scopeUser = null, array $filters = []): array
    {
        $activeStoId = app(ActiveStoService::class)->active()?->id;
        $cacheKey = 'scan_overview:' . ($scopeUser?->id ?? 'all') . ':' . md5(serialize($filters)) . ':' . $activeStoId;

        return Cache::remember($cacheKey, 15, function () use ($scopeUser, $filters) {
            $baseQuery = $this->scanQuery($scopeUser, $filters);

            return [
                'total_today' => (clone $baseQuery)
                    ->whereBetween('scan_results.created_at', [now()->startOfDay(), now()->endOfDay()])
                    ->count(),
                'valid_scans' => (clone $baseQuery)->where('keterangan', 'OK')->count(),
                'duplicate_scans' => $this->duplicateGroupCount(clone $baseQuery),
                'invalid_scans' => (clone $baseQuery)->where('keterangan', '!=', 'OK')->count(),
            ];
        });
    }

    public function activityOverview(?User $scopeUser = null, array $filters = []): Collection
    {
        return $this->scanQuery($scopeUser, $filters)
            ->join('users', 'users.id', '=', 'scan_results.user_id')
            ->selectRaw('scan_results.user_id, users.name, COUNT(*) as total')
            ->groupBy('scan_results.user_id', 'users.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name ?? 'Unknown',
                'total' => (int) $item->total,
            ]);
    }

    public function scanPerDay(?User $scopeUser = null, array $filters = [], int $days = 7): Collection
    {
        return $this->scanQuery($scopeUser, $filters)
            ->selectRaw('DATE(scan_results.created_at) as date, COUNT(*) as total')
            ->where('scan_results.created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'total' => (int) $item->total,
            ]);
    }

    /**
     * Consolidated validator overview: runs duplicateGroupQuery only ONCE
     * instead of 3 separate times (was: total_barcode, valid, need_check = 3 subqueries).
     */
    public function validatorOverview(array $filters = []): array
    {
        unset($filters['status']);

        $activeStoId = app(ActiveStoService::class)->active()?->id;
        $cacheKey = 'validator_overview:' . md5(serialize($filters)) . ':' . $activeStoId;

        return Cache::remember($cacheKey, 30, function () use ($filters) {
            $groups = $this->materialDoubleQuery->duplicateGroupQuery($filters);

            // Single query — count all three metrics at once
            $result = DB::query()
                ->fromSub($groups, 'validator_groups')
                ->selectRaw('COUNT(*) as total_barcode')
                ->selectRaw('SUM(CASE WHEN validated_at IS NOT NULL THEN 1 ELSE 0 END) as valid')
                ->selectRaw('SUM(CASE WHEN validated_at IS NULL THEN 1 ELSE 0 END) as need_check')
                ->first();

            return [
                'total_barcode' => (int) ($result->total_barcode ?? 0),
                'valid' => (int) ($result->valid ?? 0),
                'need_check' => (int) ($result->need_check ?? 0),
            ];
        });
    }

    public function validationByScanner(array $filters = []): Collection
    {
        unset($filters['status']);

        $activeStoId = app(ActiveStoService::class)->active()?->id;
        $cacheKey = 'validation_by_scanner:' . md5(serialize($filters)) . ':' . $activeStoId;

        return Cache::remember($cacheKey, 30, function () use ($filters) {
            $groups = $this->materialDoubleQuery->duplicateGroupQuery($filters);

            $query = MaterialDoubleValidation::query()
                ->joinSub($groups, 'material_double_groups', function ($join) {
                    $join->on('material_double_groups.sto_code_id', '=', 'material_double_validations.sto_code_id')
                        ->on('material_double_groups.barcode_material', '=', 'material_double_validations.barcode_material')
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
        });
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
            $query->where('scan_results.user_id', $scopeUser->id);
        }

        if (!empty($filters['plant_id'])) {
            $query->where('scan_results.plant_id', $filters['plant_id']);
        }



        if (!empty($filters['date_from'])) {
            $query->where('scan_results.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where('scan_results.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        return $query;
    }

    private function duplicateGroupCount(Builder $query): int
    {
        $groups = $query
            ->select('barcode_material', 'plant_id', 'location_id')
            ->groupBy('barcode_material', 'plant_id', 'location_id')
            ->havingRaw('COUNT(*) > 1');

        return DB::query()->fromSub($groups, 'duplicate_groups')->count();
    }
}
