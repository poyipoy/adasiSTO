<?php

namespace App\Exports;

use App\Services\MaterialDoubleQueryService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class MaterialDoubleExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private int $rowNumber = 0;

    public function __construct(private array $filters = []) {}

    public function query()
    {
        return app(MaterialDoubleQueryService::class)
            ->duplicateGroupQuery($this->filters)
            ->orderByDesc('duplicate_count')
            ->orderByDesc('scan_results.barcode_material');
    }

    public function headings(): array
    {
        return [
            'No',
            'Barcode Material',
            'Material Name',
            'Shape',
            'Size',
            'Plant',
            'Location',
            'Duplicate Count',
            'Validation Status',
            'Validated By',
            'Validated At',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $size = $row->shape_code === 'RF'
            ? "{$row->thickness} x {$row->width} x {$row->length}"
            : "⌀{$row->diameter} x {$row->length}";

        $validationStatus = $row->validated_at !== null ? 'Validated' : 'Pending';

        return [
            $this->rowNumber,
            $row->barcode_material,
            $row->material_name,
            $row->shape_name,
            $size,
            $row->plant_name ?: '-',
            $row->location_name ?: '-',
            (int) $row->duplicate_count,
            $validationStatus,
            $row->validated_by_name ?: '-',
            $row->validated_at ? \Carbon\Carbon::parse($row->validated_at)->format('Y-m-d H:i:s') : '-',
        ];
    }
}
