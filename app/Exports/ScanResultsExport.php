<?php

namespace App\Exports;

use App\Models\ScanResult;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class ScanResultsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    private Request $request;
    private int $rowNumber = 0;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = ScanResult::with(['user', 'plant', 'location', 'stoSession'])
            ->orderBy('created_at', 'desc');

        if ($this->request->filled('plant_id')) {
            $query->where('plant_id', $this->request->plant_id);
        }
        if ($this->request->filled('user_id')) {
            $query->where('user_id', $this->request->user_id);
        }
        if ($this->request->filled('sto_code')) {
            $query->whereHas('stoSession', fn($q) => $q->where('sto_code', $this->request->sto_code));
        }
        if ($this->request->filled('keterangan')) {
            $query->where('keterangan', $this->request->keterangan);
        }
        if ($this->request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $this->request->date_from);
        }
        if ($this->request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $this->request->date_to);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'Barcode',
            'Material Code',
            'Material Name',
            'Shape',
            'Thickness',
            'Width',
            'Diameter',
            'Length',
            'Qty',
            'Lot',
            'User',
            'Plant',
            'Lokasi',
            'STO Code',
            'Waktu Scan',
            'Keterangan',
        ];
    }

    public function map($scanResult): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $scanResult->barcode_material,
            $scanResult->material_code,
            $scanResult->material_name,
            $scanResult->shape_name,
            $scanResult->thickness,
            $scanResult->width,
            $scanResult->diameter,
            $scanResult->length,
            $scanResult->qty,
            $scanResult->lot ?? '-',
            $scanResult->user->name ?? '-',
            $scanResult->plant->name ?? '-',
            $scanResult->location->name ?? '-',
            $scanResult->stoSession->sto_code ?? '-',
            $scanResult->scan_time?->format('Y-m-d H:i:s'),
            $scanResult->keterangan,
        ];
    }
}
