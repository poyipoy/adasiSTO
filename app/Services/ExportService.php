<?php

namespace App\Services;

use App\Models\ScanResult;
use Illuminate\Database\Eloquent\Builder;

class ExportService
{
    public function filteredScanResults(array $filters = []): Builder
    {
        $query = ScanResult::query()
            ->with(['user', 'plant', 'location'])
            ->latestFirst();

        if (!empty($filters['sto_code'])) {
            $query->where('sto_code', $filters['sto_code']);
        }

        if (!empty($filters['plant_id'])) {
            $query->where('plant_id', $filters['plant_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['material_code'])) {
            $query->where('material_code', $filters['material_code']);
        }

        if (!empty($filters['lot_number'])) {
            $query->where('lot_number', 'like', '%' . $filters['lot_number'] . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    public function exportFilters(array $input): array
    {
        return collect($input)
            ->only(['sto_code', 'plant_id', 'location_id', 'user_id', 'material_code', 'lot_number', 'date_from', 'date_to'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }
}
