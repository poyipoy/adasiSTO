<?php

namespace App\Exports;

use App\Services\ExportService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ScanResultsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private int $rowNumber = 0;

    public function __construct(private array $filters = []) {}

    public function query()
    {
        return app(ExportService::class)->filteredScanResults($this->filters);
    }

    public function headings(): array
    {
        return [
            'No',
            'Barcode Raw',
            'Barcode Material',
            'Material Code',
            'Material Name',
            'Shape',
            'Thickness',
            'Width',
            'Diameter',
            'Length',
            'Lot Number',
            'Qty',
            'User',
            'Plant',
            'Location/Rack',
            'STO Code',
            'Scan Source',
            'Time',
            'Keterangan',
        ];
    }

    public function map($scanResult): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $scanResult->barcode_raw,
            $scanResult->barcode_material,
            $scanResult->material_code,
            $scanResult->material_name,
            $scanResult->shape_name,
            $scanResult->thickness,
            $scanResult->width,
            $scanResult->diameter,
            $scanResult->length,
            $scanResult->lot_number,
            $scanResult->qty,
            $scanResult->user->name ?? '-',
            $scanResult->plant->name ?? '-',
            $scanResult->location->name ?? '-',
            $scanResult->sto_code,
            $scanResult->scan_source,
            $scanResult->created_at?->format('Y-m-d H:i:s'),
            $scanResult->keterangan,
        ];
    }
}
