<?php

namespace App\Services;

use App\Models\MasterMaterial;
use Illuminate\Support\Facades\Log;

class BarcodeParserService
{
    private const SHAPES = [
        'RF' => 'Flat',
        'RR' => 'Round',
    ];

    public function parse(string $qr): array
    {
        $parts = array_map('trim', explode('|', $qr));

        if (count($parts) !== 3) {
            return $this->invalid('Format barcode tidak valid.');
        }

        [$barcodeMaterial, $lotNumber, $qty] = $parts;
        $barcodeMaterial = strtoupper($barcodeMaterial);

        if ($barcodeMaterial === '' || $lotNumber === '' || $qty === '') {
            return $this->invalid('Format barcode tidak valid.');
        }

        if (!ctype_digit($qty) || (int) $qty <= 0) {
            return $this->invalid('Qty tidak valid.');
        }

        if (!preg_match('/^(RF|RR)([A-Z0-9]{2})(\d{3})-(\d{8})([A-Z])$/', $barcodeMaterial, $matches)) {
            return $this->invalid('Format barcode tidak valid.');
        }

        $shapeCode = $matches[1];
        $materialCode = $matches[2];
        $primary = (int) $matches[3];
        $secondary = $matches[4];

        $material = MasterMaterial::findByCode($materialCode);

        if (!$material) {
            return $this->invalid('Kode material tidak ditemukan di Master Material.');
        }

        $firstSecondary = (int) substr($secondary, 0, 4);
        $length = (int) substr($secondary, 4, 4);


        $result = [
            'valid' => true,
            'barcode_material' => $barcodeMaterial,
            'lot_number' => $lotNumber,
            'qty' => (int) $qty,
            'shape_code' => $shapeCode,
            'shape_name' => self::SHAPES[$shapeCode],
            'material_code' => $materialCode,
            'material_name' => $material->material_name,
            'thickness' => null,
            'width' => null,
            'diameter' => null,
            'length' => $length,
        ];

        if ($shapeCode === 'RF') {
            if ($primary <= 0 || $firstSecondary <= 0 || $length <= 0) {
                return $this->invalid('Format barcode tidak valid. RF membutuhkan thickness, width, dan length yang lebih besar dari 0.');
            }

            $result['thickness'] = $primary;
            $result['width'] = $firstSecondary;

            return $result;
        }

        if ($shapeCode === 'RR') {
            if ($firstSecondary > 0) {
                return $this->invalid('Format barcode tidak valid. RR tidak boleh memiliki nilai width.');
            }
            if ($primary <= 0 || $length <= 0) {
                return $this->invalid('Format barcode tidak valid. RR membutuhkan diameter dan length yang lebih besar dari 0.');
            }

            $result['diameter'] = $primary;

            return $result;
        }

        return $this->invalid('Shape tidak dikenal.');
    }

    private function invalid(string $message): array
    {
        Log::warning('Barcode parsing failed', ['message' => $message]);

        return [
            'valid' => false,
            'message' => $message,
        ];
    }
}
