<?php

namespace App\Services;

use App\DTOs\BarcodeResult;
use App\Models\MasterMaterial;

class BarcodeParser
{
    /**
     * Shape mapping: prefix → shape name.
     */
    private const SHAPES = [
        'RF' => 'Flat',
        'RR' => 'Round',
    ];

    /**
     * Parse a barcode string and return structured data.
     *
     * Format Flat (RF): RF{material_code:2}{thickness:3}-{width:4}{length:4}{suffix:1}
     *   Example: RF1H059-00960099B
     *   Cleaned: RF1H05900960099B (17 chars)
     *   → shape=Flat, material=1H, thickness=59, width=96, length=99
     *
     * Format Round (RR): RR{material_code:2}{diameter:3}-{padding:4}{length:4}{suffix:1}
     *   Example: RR2P051-00000835B
     *   Cleaned: RR2P05100000835B (17 chars)
     *   → shape=Round, material=2P, diameter=51, length=835
     */
    public function parse(string $barcode): BarcodeResult
    {
        $originalBarcode = trim($barcode);

        // Remove dashes for positional parsing
        $clean = str_replace('-', '', $originalBarcode);

        // Validate minimum length
        if (strlen($clean) < 16) {
            return new BarcodeResult(
                barcode: $originalBarcode,
                isValid: false,
                errorMessage: 'Format Barcode Tidak Valid'
            );
        }

        // Extract shape code (first 2 characters)
        $shapeCode = strtoupper(substr($clean, 0, 2));

        if (!isset(self::SHAPES[$shapeCode])) {
            return new BarcodeResult(
                barcode: $originalBarcode,
                isValid: false,
                errorMessage: 'Format Barcode Tidak Valid'
            );
        }

        $shapeName = self::SHAPES[$shapeCode];

        // Extract material code (chars 2-3)
        $materialCode = strtoupper(substr($clean, 2, 2));

        // Lookup material from master data
        $material = MasterMaterial::findByCode($materialCode);

        if (!$material) {
            return new BarcodeResult(
                barcode: $originalBarcode,
                materialCode: $materialCode,
                shapeCode: $shapeCode,
                shapeName: $shapeName,
                isValid: false,
                errorMessage: 'Material Tidak Terdaftar'
            );
        }

        // Parse dimensions based on shape
        if ($shapeCode === 'RF') {
            return $this->parseFlat($originalBarcode, $clean, $shapeCode, $shapeName, $materialCode, $material->name);
        }

        return $this->parseRound($originalBarcode, $clean, $shapeCode, $shapeName, $materialCode, $material->name);
    }

    /**
     * Parse flat barcode (RF).
     * Cleaned format: RF{mat:2}{thick:3}{width:4}{length:4}{suffix:1}
     * Positions:       0-1     2-3     4-6    7-10    11-14    15
     */
    private function parseFlat(
        string $original,
        string $clean,
        string $shapeCode,
        string $shapeName,
        string $materialCode,
        string $materialName,
    ): BarcodeResult {
        $thickness = (float) ltrim(substr($clean, 4, 3), '0') ?: 0;
        $width = (float) ltrim(substr($clean, 7, 4), '0') ?: 0;
        $length = (float) ltrim(substr($clean, 11, 4), '0') ?: 0;

        return new BarcodeResult(
            barcode: $original,
            materialCode: $materialCode,
            materialName: $materialName,
            shapeCode: $shapeCode,
            shapeName: $shapeName,
            thickness: $thickness,
            width: $width,
            diameter: null,
            length: $length,
            isValid: true,
        );
    }

    /**
     * Parse round barcode (RR).
     * Cleaned format: RR{mat:2}{diameter:3}{padding:4}{length:4}{suffix:1}
     * Positions:       0-1     2-3      4-6     7-10      11-14     15
     */
    private function parseRound(
        string $original,
        string $clean,
        string $shapeCode,
        string $shapeName,
        string $materialCode,
        string $materialName,
    ): BarcodeResult {
        $diameter = (float) ltrim(substr($clean, 4, 3), '0') ?: 0;
        $length = (float) ltrim(substr($clean, 11, 4), '0') ?: 0;

        return new BarcodeResult(
            barcode: $original,
            materialCode: $materialCode,
            materialName: $materialName,
            shapeCode: $shapeCode,
            shapeName: $shapeName,
            thickness: null,
            width: null,
            diameter: $diameter,
            length: $length,
            isValid: true,
        );
    }
}
