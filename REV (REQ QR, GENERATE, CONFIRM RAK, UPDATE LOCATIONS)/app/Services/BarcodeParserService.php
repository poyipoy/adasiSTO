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
        $rawQr = $qr;

        // Normalisasi barcode untuk semua input (menghilangkan gap spasi yang panjang)
        $qr = preg_replace('/\s+/', ' ', $qr);
        $qr = preg_replace('/\s*\|\s*/', ' | ', $qr);
        $qr = trim($qr);
        // Hapus karakter pipa | di paling akhir jika ada
        $qr = rtrim($qr, '|');
        $qr = trim($qr);

        $parts = array_map('trim', explode('|', $qr));

        if (count($parts) !== 3) {
            return $this->invalid('Format barcode tidak valid (Bukan 3 bagian).', $rawQr);
        }

        [$barcodeMaterial, $lotNumber, $qty] = $parts;
        $barcodeMaterial = strtoupper($barcodeMaterial);

        if ($lotNumber === '') {
            $lotNumber = '-';
        }

        if ($barcodeMaterial === '' || $qty === '') {
            return $this->invalid('Format barcode tidak valid (Ada bagian kosong).', $rawQr);
        }

        if (!ctype_digit($qty) || (int) $qty <= 0) {
            return $this->invalid('Qty tidak valid.', $rawQr);
        }

        if (!preg_match('/^(RF|RR)([A-Z0-9]{2})(\d{3})-(\d{8})([A-Z])$/', $barcodeMaterial, $matches)) {
            return $this->invalid('Format barcode tidak valid (Pola Material Code).', $rawQr);
        }

        $shapeCode = $matches[1];
        $materialCode = $matches[2];
        $primary = (int) $matches[3];
        $secondary = $matches[4];

        $material = MasterMaterial::findByCode($materialCode);

        if (!$material) {
            return $this->invalid('Kode material tidak ditemukan di Master Material.', $rawQr);
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
                return $this->invalid('Format barcode tidak valid. RF membutuhkan thickness, width, dan length yang lebih besar dari 0.', $rawQr);
            }

            $result['thickness'] = $primary;
            $result['width'] = $firstSecondary;

            return $result;
        }

        if ($shapeCode === 'RR') {
            if ($firstSecondary > 0) {
                return $this->invalid('Format barcode tidak valid. RR tidak boleh memiliki nilai width.', $rawQr);
            }
            if ($primary <= 0 || $length <= 0) {
                return $this->invalid('Format barcode tidak valid. RR membutuhkan diameter dan length yang lebih besar dari 0.', $rawQr);
            }

            $result['diameter'] = $primary;

            return $result;
        }

        return $this->invalid('Shape tidak dikenal.', $rawQr);
    }

    private function invalid(string $message, string $rawQr = ''): array
    {
        Log::warning('Barcode parsing failed', ['message' => $message, 'raw_input' => $rawQr]);

        return [
            'valid' => false,
            'message' => 'Format barcode tidak valid.',
        ];
    }

    public static function normalizeSearch(?string $search): array
    {
        if (empty($search)) {
            return [
                'original' => '',
                'normalized' => '',
                'first_part' => '',
            ];
        }

        $normalized = preg_replace('/\s+/', ' ', $search);
        $normalized = preg_replace('/\s*\|\s*/', ' | ', $normalized);
        $normalized = trim($normalized);
        
        $parts = explode('|', $normalized);
        $firstPart = trim($parts[0]);
        
        return [
            'original' => $search,
            'normalized' => $normalized,
            'first_part' => $firstPart,
        ];
    }
}
