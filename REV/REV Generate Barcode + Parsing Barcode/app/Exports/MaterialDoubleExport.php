<?php

namespace App\Exports;

use App\Services\MaterialDoubleQueryService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MaterialDoubleExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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

        $size = in_array($row->shape_code, ['RF', 'RH'])
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

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '0B2545']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '17F0DE'],
                ],
            ],
        ];
    }
}
