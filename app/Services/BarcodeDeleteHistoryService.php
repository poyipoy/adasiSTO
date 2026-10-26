<?php

namespace App\Services;

use App\Models\BarcodeDeleteHistory;
use App\Models\ScanResult;
use App\Models\User;

class BarcodeDeleteHistoryService
{
    public const SOURCE_SCAN_HISTORY = 'scan_history';

    public const SOURCE_ADMIN_SCAN_RESULTS = 'admin_scan_results';

    public const SOURCE_MATERIAL_DOUBLE = 'material_double';

    public function create(
        ScanResult $scanResult,
        User $actor,
        string $source,
        ?string $reason = null,
    ): BarcodeDeleteHistory {
        $scanResult->loadMissing(['user', 'plant', 'location']);
        $snapshot = array_merge($scanResult->getAttributes(), [
            'original_user_name' => $scanResult->user?->name,
            'plant_name' => $scanResult->plant?->name,
            'location_name' => $scanResult->location?->name,
        ]);

        return BarcodeDeleteHistory::create([
            'original_scan_result_id' => $scanResult->id,
            'sto_code_id' => $scanResult->sto_code_id,
            'sto_code' => $scanResult->sto_code,
            'deleted_by_user_id' => $actor->id,
            'deleted_by_name' => $actor->name,
            'original_user_id' => $scanResult->user_id,
            'original_user_name' => $scanResult->user?->name,
            'plant_id' => $scanResult->plant_id,
            'plant_name' => $scanResult->plant?->name,
            'location_id' => $scanResult->location_id,
            'location_name' => $scanResult->location?->name,
            'barcode_raw' => $scanResult->barcode_raw,
            'barcode_material' => $scanResult->barcode_material,
            'material_code' => $scanResult->material_code,
            'material_name' => $scanResult->material_name,
            'shape_code' => $scanResult->shape_code,
            'shape_name' => ShapeFormatter::name($scanResult->shape_code, $scanResult->shape_name),
            'thickness' => $scanResult->thickness,
            'width' => $scanResult->width,
            'diameter' => $scanResult->diameter,
            'length' => $scanResult->length,
            'lot_number' => $scanResult->lot_number,
            'qty' => $scanResult->qty,
            'keterangan' => $scanResult->keterangan,
            'scan_source' => $scanResult->scan_source,
            'deletion_source' => $source,
            'deletion_reason' => $reason,
            'snapshot' => $snapshot,
            'original_scanned_at' => $scanResult->created_at,
            'deleted_at' => now(),
        ]);
    }
}
