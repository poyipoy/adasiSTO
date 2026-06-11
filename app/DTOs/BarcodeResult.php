<?php

namespace App\DTOs;

class BarcodeResult
{
    public string $barcode;
    public string $materialCode;
    public string $materialName;
    public string $shapeCode;
    public string $shapeName;
    public ?float $thickness;
    public ?float $width;
    public ?float $diameter;
    public ?float $length;
    public bool $isValid;
    public ?string $errorMessage;

    public function __construct(
        string $barcode = '',
        string $materialCode = '',
        string $materialName = '',
        string $shapeCode = '',
        string $shapeName = '',
        ?float $thickness = null,
        ?float $width = null,
        ?float $diameter = null,
        ?float $length = null,
        bool $isValid = false,
        ?string $errorMessage = null,
    ) {
        $this->barcode = $barcode;
        $this->materialCode = $materialCode;
        $this->materialName = $materialName;
        $this->shapeCode = $shapeCode;
        $this->shapeName = $shapeName;
        $this->thickness = $thickness;
        $this->width = $width;
        $this->diameter = $diameter;
        $this->length = $length;
        $this->isValid = $isValid;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Convert to array for storage in scan_results table.
     */
    public function toArray(): array
    {
        return [
            'barcode_material' => $this->barcode,
            'material_code' => $this->materialCode,
            'material_name' => $this->materialName,
            'shape_code' => $this->shapeCode,
            'shape_name' => $this->shapeName,
            'thickness' => $this->thickness,
            'width' => $this->width,
            'diameter' => $this->diameter,
            'length' => $this->length,
        ];
    }
}
