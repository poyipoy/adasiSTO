# DATABASE.md — STO (Scan To Office)

## 1. Purpose

Dokumen ini mendefinisikan struktur database untuk sistem STO berbasis Laravel 12 + MySQL.

Database harus mendukung:

* Scan QR/Barcode material.
* Validasi master material.
* Active STO.
* Master Plant dan Location.
* Pemisahan data antar user.
* Admin monitoring.
* Export Excel/PDF.
* Data besar 10.000–100.000+ row.
* Audit log untuk perubahan penting.

---

# 2. Database Engine

Recommended:

```text
MySQL 8.x
InnoDB
utf8mb4_unicode_ci
```

---

# 3. Required Tables

```text
users
sto_codes
plants
locations
master_materials
master_keterangan
scan_results
scan_result_logs
```

Optional:

```text
roles
permissions
export_logs
failed_scan_logs
```

---

# 4. Entity Relationship Overview

```text
users
  └── scan_results

sto_codes
  └── scan_results

plants
  ├── locations
  └── scan_results

locations
  └── scan_results

master_materials
  └── scan_results via material_code

scan_results
  └── scan_result_logs
```

---

# 5. Table: users

Digunakan untuk login user dan admin.

## Columns

```text
id
name
username
password
role
is_active
created_at
updated_at
```

## Notes

Role minimal:

```text
admin
scanner
```

PIC otomatis berasal dari user yang login.

---

# 6. Table: sto_codes

Digunakan untuk Master STO.

## Columns

```text
id
code
description
start_date
end_date
is_active
created_at
updated_at
```

## Example

| code    | is_active |
| ------- | --------- |
| STO2606 | 0         |
| STO2607 | 1         |
| STO2608 | 0         |

## Business Rule

Hanya boleh ada satu STO aktif.

Jika admin mengaktifkan STO baru, STO aktif sebelumnya wajib otomatis menjadi inactive.

---

# 7. Table: plants

Master Plant.

## Columns

```text
id
name
is_active
created_at
updated_at
```

## Example

```text
Cikarang
Deltamas
Surabaya
```

---

# 8. Table: locations

Master Location/Rack.

## Columns

```text
id
plant_id
code
name
is_active
created_at
updated_at
```

## Relationship

```text
plants.id = locations.plant_id
```

## Example

```text
Plant Cikarang
- CT01
- CT02
- CT03
```

---

# 9. Table: master_materials

Master kode material.

## Columns

```text
id
material_code
material_name
is_active
created_at
updated_at
```

## Example

| material_code | material_name |
| ------------- | ------------- |
| 1H            | SKD11         |
| 2P            | SKD61         |
| 2L            | DHAW          |
| 4F            | P20           |
| 4E            | NAK80         |
| 1B            | DC53          |

## Rule

Nama material tidak boleh diinput manual dari hasil scan.

Sistem wajib lookup:

```text
material_code → material_name
```

---

# 10. Table: master_keterangan

Master status/keterangan scan.

## Columns

```text
id
name
is_active
created_at
updated_at
```

## Default Data

```text
OK
Lot Salah
Size Salah
Material Salah
```

## Rule

Saat scan berhasil:

```text
keterangan = OK
```

User tidak bisa mengubah keterangan.

Admin bisa mengubah keterangan.

---

# 11. Table: scan_results

Tabel utama hasil scan.

## Columns

```text
id

user_id
sto_code_id
plant_id
location_id

sto_code

barcode_raw
barcode_material
lot_number
qty

material_code
material_name

shape_code
shape_name

thickness
width
diameter
length

keterangan
scan_source

created_at
updated_at
```

## Column Explanation

| Column           | Description                     |
| ---------------- | ------------------------------- |
| user_id          | User yang melakukan scan        |
| sto_code_id      | Relasi ke STO aktif             |
| plant_id         | Plant yang dipilih user         |
| location_id      | Location/Rack yang dipilih user |
| sto_code         | Snapshot kode STO saat scan     |
| barcode_raw      | QR asli yang discan             |
| barcode_material | Bagian pertama QR               |
| lot_number       | Bagian kedua QR                 |
| qty              | Bagian ketiga QR                |
| material_code    | Hasil parsing barcode material  |
| material_name    | Lookup dari Master Material     |
| shape_code       | RF/RR                           |
| shape_name       | Flat/Round                      |
| thickness        | Untuk Flat                      |
| width            | Untuk Flat                      |
| diameter         | Untuk Round                     |
| length           | Panjang material                |
| keterangan       | Default OK                      |
| scan_source      | camera/scanner_gun/manual       |

## Important Rule

`sto_code` disimpan sebagai snapshot string agar histori scan tetap aman walaupun Master STO berubah.

---

# 12. Table: scan_result_logs

Audit log perubahan data scan.

## Columns

```text
id
scan_result_id
user_id
action
field_name
old_value
new_value
created_at
```

## Action

```text
created
updated
deleted
```

## Notes

Walaupun user delete scan menggunakan hard delete, log delete harus dibuat terlebih dahulu.

---

# 13. Recommended MySQL Migration Structure

## sto_codes

```php
Schema::create('sto_codes', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->string('description')->nullable();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->boolean('is_active')->default(false);
    $table->timestamps();

    $table->index('is_active');
});
```

---

## plants

```php
Schema::create('plants', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

---

## locations

```php
Schema::create('locations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
    $table->string('code');
    $table->string('name')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->unique(['plant_id', 'code']);
    $table->index('plant_id');
});
```

---

## master_materials

```php
Schema::create('master_materials', function (Blueprint $table) {
    $table->id();
    $table->string('material_code')->unique();
    $table->string('material_name');
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index('material_code');
});
```

---

## master_keterangan

```php
Schema::create('master_keterangan', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

---

## scan_results

```php
Schema::create('scan_results', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sto_code_id')->nullable()->constrained('sto_codes')->nullOnDelete();
    $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
    $table->foreignId('location_id')->constrained()->cascadeOnDelete();

    $table->string('sto_code');

    $table->string('barcode_raw');
    $table->string('barcode_material');
    $table->string('lot_number');
    $table->unsignedInteger('qty')->default(1);

    $table->string('material_code');
    $table->string('material_name');

    $table->string('shape_code', 10);
    $table->string('shape_name', 50);

    $table->unsignedInteger('thickness')->nullable();
    $table->unsignedInteger('width')->nullable();
    $table->unsignedInteger('diameter')->nullable();
    $table->unsignedInteger('length')->nullable();

    $table->string('keterangan')->default('OK');
    $table->string('scan_source')->nullable();

    $table->timestamps();

    $table->index('user_id');
    $table->index('sto_code_id');
    $table->index('plant_id');
    $table->index('location_id');
    $table->index('sto_code');
    $table->index('barcode_material');
    $table->index('material_code');
    $table->index('shape_code');
    $table->index('created_at');
    $table->index(['user_id', 'created_at']);
    $table->index(['sto_code', 'barcode_material']);
});
```

---

## scan_result_logs

```php
Schema::create('scan_result_logs', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('scan_result_id')->nullable();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

    $table->string('action');
    $table->string('field_name')->nullable();
    $table->text('old_value')->nullable();
    $table->text('new_value')->nullable();

    $table->timestamp('created_at')->useCurrent();

    $table->index('scan_result_id');
    $table->index('user_id');
    $table->index('action');
    $table->index('created_at');
});
```

---

# 14. Active STO Enforcement

Recommended service logic:

```php
DB::transaction(function () use ($stoCode) {
    StoCode::query()->update(['is_active' => false]);

    $stoCode->update(['is_active' => true]);
});
```

Do not allow multiple active STO records.

---

# 15. Delete Rules

User delete:

```text
Hard delete
Only own scan
Create audit log before delete
```

Admin delete:

```text
Hard delete
Any scan
Create audit log before delete
```

---

# 16. Performance Rules

Admin tables must use:

```text
Server-side processing
Pagination
Filter
Search
Sorting
```

Do not use:

```php
ScanResult::all();
```

for main admin pages.

---

# 17. Required Index Summary

```sql
INDEX(user_id)
INDEX(sto_code_id)
INDEX(plant_id)
INDEX(location_id)
INDEX(sto_code)
INDEX(barcode_material)
INDEX(material_code)
INDEX(shape_code)
INDEX(created_at)
INDEX(user_id, created_at)
INDEX(sto_code, barcode_material)
```

---

# 18. Initial Seeder Data

## Master Keterangan

```text
OK
Lot Salah
Size Salah
Material Salah
```

## Plant

```text
Cikarang
Deltamas
Surabaya
```

## Shape Mapping

Stored in code/parser:

```text
RF = Flat
RR = Round
```

---

# 19. Final Database Principle

Database harus mendukung:

```text
Fast scan
Fast lookup
Large data table
Clear ownership
Reliable monitoring
Admin reporting
```
