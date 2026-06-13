# BARCODE_PARSING.md — STO Barcode & QR Parsing Specification

## 1. Purpose

Dokumen ini menjelaskan aturan parsing QR/Barcode untuk sistem STO.

Parser wajib dibuat terpisah di:

```text
app/Services/BarcodeParserService.php
```

Controller tidak boleh berisi logic parsing panjang.

---

# 2. Final QR Structure

Format final QR:

```text
<barcode_material>|<lot_number>|<qty>
```

Contoh:

```text
RF1H059-00960099B|ST2605|1
```

QR juga dapat mengandung spasi di sekitar separator:

```text
RF1H059-00960099B | ST2605 | 1
```

Parser harus melakukan trim setiap bagian.

---

# 3. QR Components

| Part             | Example           | Description                  |
| ---------------- | ----------------- | ---------------------------- |
| barcode_material | RF1H059-00960099B | Barcode material dan dimensi |
| lot_number       | ST2605            | Lot material                 |
| qty              | 1                 | Quantity                     |

Important:

```text
STO Code tidak berasal dari QR.
STO Code berasal dari Master STO aktif.
```

---

# 4. Valid QR Example

```text
RF1H059-00960099B|ST2605|1
```

Expected:

```text
barcode_material = RF1H059-00960099B
lot_number       = ST2605
qty              = 1
```

---

# 5. Invalid QR Example

Missing qty:

```text
RF1H059-00960099B|ST2605
```

Missing lot and qty:

```text
RF1H059-00960099B
```

Empty part:

```text
RF1H059-00960099B||1
```

Invalid qty:

```text
RF1H059-00960099B|ST2605|ABC
```

Qty zero:

```text
RF1H059-00960099B|ST2605|0
```

---

# 6. Material Barcode Structure

Example:

```text
RF1H059-00960099B
```

General structure:

```text
[SHAPE_CODE][MATERIAL_CODE][PRIMARY_DIMENSION]-[SECONDARY_DIMENSION][SUFFIX]
```

Specific example:

```text
RF 1H 059 - 0096 0099 B
```

| Segment                    | Value | Meaning   |
| -------------------------- | ----- | --------- |
| Shape Code                 | RF    | Flat      |
| Material Code              | 1H    | SKD11     |
| Primary Dimension          | 059   | Thickness |
| Secondary Dimension Part 1 | 0096  | Width     |
| Secondary Dimension Part 2 | 0099  | Length    |
| Suffix                     | B     | Suffix    |

---

# 7. Supported Shape Codes

Only two shapes are supported:

| Code | Shape |
| ---- | ----- |
| RF   | Flat  |
| RR   | Round |

Any other shape code must be rejected.

---

# 8. RF — Flat Parsing Rule

Example:

```text
RF1H059-00960099B
```

Expected:

| Field         | Value |
| ------------- | ----- |
| shape_code    | RF    |
| shape_name    | Flat  |
| material_code | 1H    |
| thickness     | 59    |
| width         | 96    |
| diameter      | null  |
| length        | 99    |

Rule:

```text
RF = Flat
Characters 1-2 = shape_code
Characters 3-4 = material_code
Next 3 digits = thickness
After "-" first 4 digits = width
After "-" last 4 digits = length
Final suffix = ignored for dimension
```

---

# 9. RR — Round Parsing Rule

Example:

```text
RR2P051-00000835B
```

Expected:

| Field         | Value |
| ------------- | ----- |
| shape_code    | RR    |
| shape_name    | Round |
| material_code | 2P    |
| thickness     | null  |
| width         | null  |
| diameter      | 51    |
| length        | 835   |

Rule:

```text
RR = Round
Characters 1-2 = shape_code
Characters 3-4 = material_code
Next 3 digits = diameter
After "-" last 4 digits = length
```

For Round:

```text
width = null
thickness = null
```

---

# 10. Material Lookup Rule

After parsing `material_code`, lookup Master Material.

Example:

```text
1H → SKD11
2P → SKD61
```

If material code is not found:

```text
Reject scan
```

Message:

```text
Kode material tidak ditemukan di Master Material.
```

---

# 11. Recommended Regex

## QR Split

```php
$parts = array_map('trim', explode('|', $qr));
```

Required:

```php
count($parts) === 3
```

---

## Material Barcode Basic Pattern

```regex
/^(RF|RR)([A-Z0-9]{2})(\d{3})-(\d{8})([A-Z])$/
```

Captures:

```text
1 = shape_code
2 = material_code
3 = primary_dimension
4 = secondary_dimension
5 = suffix
```

---

# 12. Dimension Parsing

## Secondary Dimension

Example:

```text
00960099
```

Split:

```text
0096
0099
```

Implementation:

```php
$widthOrUnused = intval(substr($secondary, 0, 4));
$length = intval(substr($secondary, 4, 4));
```

---

# 13. Parser Success Response

Example Flat:

```php
[
    'valid' => true,
    'barcode_material' => 'RF1H059-00960099B',
    'lot_number' => 'ST2605',
    'qty' => 1,
    'shape_code' => 'RF',
    'shape_name' => 'Flat',
    'material_code' => '1H',
    'material_name' => 'SKD11',
    'thickness' => 59,
    'width' => 96,
    'diameter' => null,
    'length' => 99,
]
```

Example Round:

```php
[
    'valid' => true,
    'barcode_material' => 'RR2P051-00000835B',
    'lot_number' => 'ST2605',
    'qty' => 1,
    'shape_code' => 'RR',
    'shape_name' => 'Round',
    'material_code' => '2P',
    'material_name' => 'SKD61',
    'thickness' => null,
    'width' => null,
    'diameter' => 51,
    'length' => 835,
]
```

---

# 14. Parser Failed Response

```php
[
    'valid' => false,
    'message' => 'Format barcode tidak valid.'
]
```

Other messages:

```text
Kode material tidak ditemukan di Master Material.
Qty tidak valid.
Lot tidak boleh kosong.
Shape tidak dikenal.
```

---

# 15. BarcodeParserService Contract

Location:

```text
app/Services/BarcodeParserService.php
```

Recommended method:

```php
public function parse(string $qr): array
```

Optional methods:

```php
private function parseQrStructure(string $qr): array
private function parseMaterialBarcode(string $barcodeMaterial): array
private function resolveMaterialName(string $materialCode): ?string
private function parseShape(string $shapeCode): string
```

---

# 16. Pseudocode

```php
public function parse(string $qr): array
{
    $parts = array_map('trim', explode('|', $qr));

    if (count($parts) !== 3) {
        return [
            'valid' => false,
            'message' => 'Format barcode tidak valid.'
        ];
    }

    [$barcodeMaterial, $lotNumber, $qty] = $parts;

    if ($barcodeMaterial === '' || $lotNumber === '' || $qty === '') {
        return [
            'valid' => false,
            'message' => 'Format barcode tidak valid.'
        ];
    }

    if (!ctype_digit($qty) || (int) $qty <= 0) {
        return [
            'valid' => false,
            'message' => 'Qty tidak valid.'
        ];
    }

    if (!preg_match('/^(RF|RR)([A-Z0-9]{2})(\d{3})-(\d{8})([A-Z])$/', $barcodeMaterial, $matches)) {
        return [
            'valid' => false,
            'message' => 'Format barcode tidak valid.'
        ];
    }

    $shapeCode = $matches[1];
    $materialCode = $matches[2];
    $primary = (int) $matches[3];
    $secondary = $matches[4];

    $material = MasterMaterial::where('material_code', $materialCode)
        ->where('is_active', true)
        ->first();

    if (!$material) {
        return [
            'valid' => false,
            'message' => 'Kode material tidak ditemukan di Master Material.'
        ];
    }

    $firstSecondary = (int) substr($secondary, 0, 4);
    $length = (int) substr($secondary, 4, 4);

    if ($shapeCode === 'RF') {
        return [
            'valid' => true,
            'barcode_material' => $barcodeMaterial,
            'lot_number' => $lotNumber,
            'qty' => (int) $qty,
            'shape_code' => 'RF',
            'shape_name' => 'Flat',
            'material_code' => $materialCode,
            'material_name' => $material->material_name,
            'thickness' => $primary,
            'width' => $firstSecondary,
            'diameter' => null,
            'length' => $length,
        ];
    }

    if ($shapeCode === 'RR') {
        return [
            'valid' => true,
            'barcode_material' => $barcodeMaterial,
            'lot_number' => $lotNumber,
            'qty' => (int) $qty,
            'shape_code' => 'RR',
            'shape_name' => 'Round',
            'material_code' => $materialCode,
            'material_name' => $material->material_name,
            'thickness' => null,
            'width' => null,
            'diameter' => $primary,
            'length' => $length,
        ];
    }

    return [
        'valid' => false,
        'message' => 'Shape tidak dikenal.'
    ];
}
```

---

# 17. Test Cases

## Valid Flat

Input:

```text
RF1H059-00960099B|ST2605|1
```

Expected:

```text
valid = true
shape_name = Flat
material_code = 1H
thickness = 59
width = 96
length = 99
lot_number = ST2605
qty = 1
```

---

## Valid Round

Input:

```text
RR2P051-00000835B|ST2605|1
```

Expected:

```text
valid = true
shape_name = Round
material_code = 2P
diameter = 51
length = 835
lot_number = ST2605
qty = 1
```

---

## Invalid Missing Qty

Input:

```text
RF1H059-00960099B|ST2605
```

Expected:

```text
valid = false
```

---

## Invalid Unknown Shape

Input:

```text
XX1H059-00960099B|ST2605|1
```

Expected:

```text
valid = false
```

---

## Invalid Unknown Material

Input:

```text
RFZZ059-00960099B|ST2605|1
```

Expected:

```text
valid = false
```

---

# 18. Final Parser Principle

Parser harus:

```text
Strict
Predictable
Separated from controller
Fully tested
Dependent on Master Material
```
