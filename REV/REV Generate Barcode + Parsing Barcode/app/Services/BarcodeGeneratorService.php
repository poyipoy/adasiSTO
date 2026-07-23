<?php

namespace App\Services;

use App\Models\MasterMaterial;

/**
 * Builds a barcode_material string from structured input.
 *
 * Format (from AGENTS.md):
 *   {ShapeCode}{MaterialCode}{Primary:3 digit}-{Secondary:8 digit}{CheckLetter}
 *   ShapeCode    = RF (Flat) | RR (Round)
 *   MaterialCode = 2 char alphanumeric
 *   Primary      = 3 digit — thickness (RF) | diameter (RR)
 *   Secondary    = 8 digit — width(4)+length(4)  [RR: width="0000"]
 *   CheckLetter  = 1 uppercase letter, always 'B'
 *                  TODO: no known generation logic — defaulting to 'B' (same as all existing data)
 *
 * The result of build() must round-trip through BarcodeParserService::parse() unchanged.
 */
class BarcodeGeneratorService
{
    private const CHECK_LETTER = 'B'; // TODO: unknown logic — see AGENTS.md "Format Barcode"

    /**
     * Build a barcode_material string from structured data.
     *
     * @param array{
     *   shape_code: string,
     *   material_code: string,
     *   thickness: int|null,
     *   width: int|null,
     *   diameter: int|null,
     *   length: int,
     * } $data
     *
     * @return array{valid: bool, barcode_material: string|null, errors: array<string>}
     */
    public function build(array $data): array
    {
        $errors = [];

        $shapeCode    = strtoupper(trim($data['shape_code'] ?? ''));
        $materialCode = strtoupper(trim($data['material_code'] ?? ''));

        // --- 1. Validate shape ---
        if (!in_array($shapeCode, ['RF', 'RR', 'RH'], true)) {
            $errors[] = "Shape code tidak valid. Harus RF, RR, atau RH.";
        }

        // --- 2. Validate material exists and is active ---
        $material = null;
        if ($materialCode !== '') {
            $material = MasterMaterial::findByCode($materialCode);
            if (!$material) {
                $errors[] = "Kode material '{$materialCode}' tidak ditemukan atau tidak aktif di Master Material.";
            }
        } else {
            $errors[] = "Kode material tidak boleh kosong.";
        }

        if (!empty($errors)) {
            return ['valid' => false, 'barcode_material' => null, 'errors' => $errors];
        }

        // --- 3. Validate dimensions by shape ---
        if (in_array($shapeCode, ['RF', 'RH'])) {
            $thickness = (int) ($data['thickness'] ?? 0);
            $width     = (int) ($data['width'] ?? 0);
            $length    = (int) ($data['length'] ?? 0);

            if ($thickness <= 0) {
                $errors[] = "Thickness harus lebih dari 0 untuk shape {$shapeCode}.";
            }
            if ($width <= 0) {
                $errors[] = "Width harus lebih dari 0 untuk shape {$shapeCode}.";
            }
            if ($length <= 0) {
                $errors[] = "Length harus lebih dari 0.";
            }

            if (!empty($errors)) {
                return ['valid' => false, 'barcode_material' => null, 'errors' => $errors];
            }

            // Overflow guard (max 3 digits primary, 4 digits secondary)
            if ($thickness > 999) {
                $errors[] = "Thickness melebihi batas 3 digit (max 999).";
            }
            if ($width > 9999) {
                $errors[] = "Width melebihi batas 4 digit (max 9999).";
            }
            if ($length > 9999) {
                $errors[] = "Length melebihi batas 4 digit (max 9999).";
            }

            if (!empty($errors)) {
                return ['valid' => false, 'barcode_material' => null, 'errors' => $errors];
            }

            $primary   = str_pad((string) $thickness, 3, '0', STR_PAD_LEFT);
            $secondary = str_pad((string) $width, 4, '0', STR_PAD_LEFT)
                       . str_pad((string) $length, 4, '0', STR_PAD_LEFT);

        } elseif ($shapeCode === 'RR') {
            $diameter = (int) ($data['diameter'] ?? 0);
            $length   = (int) ($data['length'] ?? 0);

            if ($diameter <= 0) {
                $errors[] = "Diameter harus lebih dari 0 untuk shape RR.";
            }
            if ($length <= 0) {
                $errors[] = "Length harus lebih dari 0.";
            }

            if (!empty($errors)) {
                return ['valid' => false, 'barcode_material' => null, 'errors' => $errors];
            }

            if ($diameter > 999) {
                $errors[] = "Diameter melebihi batas 3 digit (max 999).";
            }
            if ($length > 9999) {
                $errors[] = "Length melebihi batas 4 digit (max 9999).";
            }

            if (!empty($errors)) {
                return ['valid' => false, 'barcode_material' => null, 'errors' => $errors];
            }

            $primary   = str_pad((string) $diameter, 3, '0', STR_PAD_LEFT);
            $secondary = '0000' . str_pad((string) $length, 4, '0', STR_PAD_LEFT);
        } else {
            return ['valid' => false, 'barcode_material' => null, 'errors' => ["Shape tidak dikenal."]];
        }

        // --- 4. Assemble barcode string ---
        // Format: {ShapeCode}{MaterialCode}{Primary:3}-{Secondary:8}{CheckLetter}
        $barcodeMaterial = $shapeCode . $materialCode . $primary . '-' . $secondary . self::CHECK_LETTER;

        return [
            'valid'            => true,
            'barcode_material' => $barcodeMaterial,
            'errors'           => [],
        ];
    }
}
