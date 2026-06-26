<?php

namespace App\Services;

use App\Models\ScanResult;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class MaterialDoubleQueryService
{
    public function duplicateGroupQuery(array $filters = []): Builder
    {
        $query = ScanResult::query()
            ->leftJoin('plants', 'scan_results.plant_id', '=', 'plants.id')
            ->leftJoin('locations', 'scan_results.location_id', '=', 'locations.id')
            ->leftJoin('material_double_validations as mdv', function ($join) {
                $join->on('mdv.barcode_material', '=', 'scan_results.barcode_material')
                    ->on('mdv.plant_id', '=', 'scan_results.plant_id')
                    ->on('mdv.location_id', '=', 'scan_results.location_id');
            })
            ->leftJoin('users as mdv_user', 'mdv.validated_by', '=', 'mdv_user.id')
            ->selectRaw('
                scan_results.barcode_material,
                scan_results.material_name,
                scan_results.shape_code,
                scan_results.shape_name,
                scan_results.thickness,
                scan_results.width,
                scan_results.diameter,
                scan_results.length,
                scan_results.plant_id,
                scan_results.location_id,
                plants.name as plant_name,
                locations.name as location_name,
                COUNT(*) as duplicate_count,
                MAX(mdv.validated_at) as validated_at,
                MAX(mdv_user.name) as validated_by_name
            ')
            ->groupBy(
                'scan_results.barcode_material',
                'scan_results.material_name',
                'scan_results.shape_code',
                'scan_results.shape_name',
                'scan_results.thickness',
                'scan_results.width',
                'scan_results.diameter',
                'scan_results.length',
                'scan_results.plant_id',
                'scan_results.location_id',
                'plants.name',
                'locations.name',
            )
            ->havingRaw('COUNT(*) > 1');

        $this->applyScanFilters($query, $filters, 'scan_results.');

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'valid') {
                $query->havingRaw('MAX(mdv.validated_at) IS NOT NULL');
            } elseif ($filters['status'] === 'need_check') {
                $query->havingRaw('MAX(mdv.validated_at) IS NULL');
            }
        }

        return $query;
    }

    public function applyScanFilters($query, array $filters, string $tablePrefix = ''): void
    {


        if (!empty($filters['plant_id'])) {
            $query->where($tablePrefix . 'plant_id', $filters['plant_id']);
        }

        if (!empty($filters['location_name'])) {
            $query->whereHas('location', function ($locationQuery) use ($filters) {
                $locationQuery->where('name', $filters['location_name']);
            });
        }

        if (!empty($filters['material_code'])) {
            $query->where($tablePrefix . 'material_code', $filters['material_code']);
        }

        if (!empty($filters['date_from'])) {
            $query->where($tablePrefix . 'created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (!empty($filters['date_to'])) {
            $query->where($tablePrefix . 'created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
    }
}
