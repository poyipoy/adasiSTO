# API_SPECIFICATION.md — STO Web-Based System

## 1. Purpose

Dokumen ini mendefinisikan endpoint internal untuk sistem STO berbasis Laravel 12.

Walaupun sistem menggunakan Blade, endpoint API/AJAX tetap diperlukan untuk:

* Scanner.
* DataTables server-side.
* Master data dropdown.
* Export.
* Duplicate checking.
* Inline editing.

---

# 2. Response Standard

## Success

```json
{
  "success": true,
  "message": "Data berhasil diproses.",
  "data": {}
}
```

## Failed

```json
{
  "success": false,
  "message": "Format barcode tidak valid."
}
```

## Validation Error

```json
{
  "success": false,
  "message": "Validasi gagal.",
  "errors": {}
}
```

---

# 3. Authentication

Login menggunakan:

```text
username
password
```

Routes can use Laravel session authentication.

---

# 4. Role Middleware

Recommended middleware:

```text
auth
role:admin
role:scanner
```

---

# 5. Scanner User Endpoints

## 5.1 GET /scan/setup

Purpose:

Menampilkan halaman setup scan.

Response:

Blade page.

Data required:

```text
authenticated user
active STO
active plants
```

If no active STO:

Display:

```text
Tidak ada STO aktif yang tersedia. Silakan hubungi Admin.
```

---

## 5.2 GET /api/locations

Purpose:

Mengambil location berdasarkan plant.

Query:

```text
plant_id
```

Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "CT01",
      "name": "CT01"
    }
  ]
}
```

---

## 5.3 POST /api/scan/preview

Purpose:

Preview hasil parsing QR sebelum disimpan.

Request:

```json
{
  "qr": "RF1H059-00960099B|ST2605|1"
}
```

Response success:

```json
{
  "success": true,
  "data": {
    "barcode_material": "RF1H059-00960099B",
    "lot_number": "ST2605",
    "qty": 1,
    "shape_code": "RF",
    "shape_name": "Flat",
    "material_code": "1H",
    "material_name": "SKD11",
    "thickness": 59,
    "width": 96,
    "diameter": null,
    "length": 99
  }
}
```

Response failed:

```json
{
  "success": false,
  "message": "Format barcode tidak valid."
}
```

---

## 5.4 POST /api/scan/check-duplicate

Purpose:

Cek apakah barcode pernah discan.

Request:

```json
{
  "barcode_material": "RF1H059-00960099B"
}
```

Response duplicate:

```json
{
  "success": true,
  "duplicate": true,
  "message": "Barcode sudah pernah discan sebelumnya."
}
```

Response not duplicate:

```json
{
  "success": true,
  "duplicate": false
}
```

---

## 5.5 POST /api/scan/store

Purpose:

Menyimpan hasil scan.

Request:

```json
{
  "qr": "RF1H059-00960099B|ST2605|1",
  "plant_id": 1,
  "location_id": 1,
  "scan_source": "camera",
  "force_save": false
}
```

Notes:

`force_save = true` digunakan ketika user tetap ingin menyimpan barcode duplicate.

Validation:

* Active STO must exist.
* Plant required.
* Location required.
* QR valid.
* Material exists.
* If duplicate and force_save false, return duplicate warning.

Response success:

```json
{
  "success": true,
  "message": "Scan berhasil disimpan.",
  "data": {
    "id": 1001,
    "barcode_material": "RF1H059-00960099B",
    "material_name": "SKD11",
    "shape_name": "Flat",
    "lot_number": "ST2605",
    "qty": 1,
    "sto_code": "STO2607",
    "keterangan": "OK"
  }
}
```

Response duplicate warning:

```json
{
  "success": false,
  "duplicate": true,
  "message": "Barcode sudah pernah discan sebelumnya. Tetap simpan?"
}
```

---

## 5.6 GET /api/scan/history

Purpose:

Mengambil history scan milik user login.

Query:

```text
date_from
date_to
search
page
per_page
```

Important:

User only sees own data.

Backend filter:

```php
where('user_id', auth()->id())
```

Response:

```json
{
  "success": true,
  "data": [],
  "meta": {
    "page": 1,
    "per_page": 25,
    "total": 100
  }
}
```

---

## 5.7 DELETE /api/scan/{id}

Purpose:

User delete scan miliknya sendiri.

Rule:

```text
Hard delete
Only own scan
Create audit log before delete
```

Response:

```json
{
  "success": true,
  "message": "Scan berhasil dihapus."
}
```

Forbidden:

```json
{
  "success": false,
  "message": "Anda tidak memiliki akses untuk menghapus data ini."
}
```

---

# 6. Admin Endpoints

## 6.1 GET /admin/dashboard

Purpose:

Dashboard admin.

Data:

```text
total_scan_today
total_scan_month
total_valid
total_duplicate
total_per_user
total_per_plant
top_material
latest_scan
```

---

## 6.2 GET /admin/scan-results

Purpose:

Admin page for scan results.

Response:

Blade page.

---

## 6.3 GET /admin/api/scan-results

Purpose:

DataTables server-side endpoint.

Query:

```text
draw
start
length
search[value]
order
sto_code
plant_id
location_id
user_id
material_code
lot_number
date_from
date_to
```

Response:

DataTables standard:

```json
{
  "draw": 1,
  "recordsTotal": 100000,
  "recordsFiltered": 500,
  "data": []
}
```

Rule:

Do not use:

```php
ScanResult::all();
```

---

## 6.4 PUT /admin/api/scan-results/{id}

Purpose:

Admin update scan result.

Allowed fields:

```text
keterangan
plant_id
location_id
```

Response:

```json
{
  "success": true,
  "message": "Data scan berhasil diperbarui."
}
```

Audit log required.

---

## 6.5 DELETE /admin/api/scan-results/{id}

Purpose:

Admin delete scan.

Rule:

```text
Hard delete
Audit log before delete
```

Response:

```json
{
  "success": true,
  "message": "Data scan berhasil dihapus."
}
```

---

# 7. Master STO Endpoints

## 7.1 GET /admin/master-sto

Blade page.

---

## 7.2 GET /admin/api/master-sto

DataTables endpoint.

---

## 7.3 POST /admin/api/master-sto

Request:

```json
{
  "code": "STO2607",
  "description": "STO Juni 2026",
  "start_date": "2026-06-01",
  "end_date": "2026-06-30",
  "is_active": false
}
```

---

## 7.4 PUT /admin/api/master-sto/{id}

Update STO.

---

## 7.5 POST /admin/api/master-sto/{id}/activate

Purpose:

Aktifkan STO.

Rule:

If one STO is activated, all other STO must become inactive.

Response:

```json
{
  "success": true,
  "message": "STO berhasil diaktifkan."
}
```

---

## 7.6 DELETE /admin/api/master-sto/{id}

Delete STO if not used.

If already used in scan_results, prevent delete or allow only inactive depending on implementation decision.

Recommended:

```text
Prevent delete if used.
```

---

# 8. Master Plant Endpoints

```text
GET    /admin/master-plant
GET    /admin/api/master-plant
POST   /admin/api/master-plant
PUT    /admin/api/master-plant/{id}
DELETE /admin/api/master-plant/{id}
```

Required fields:

```text
name
is_active
```

---

# 9. Master Location Endpoints

```text
GET    /admin/master-location
GET    /admin/api/master-location
POST   /admin/api/master-location
PUT    /admin/api/master-location/{id}
DELETE /admin/api/master-location/{id}
```

Required fields:

```text
plant_id
code
name
is_active
```

---

# 10. Master Material Endpoints

```text
GET    /admin/master-material
GET    /admin/api/master-material
POST   /admin/api/master-material
PUT    /admin/api/master-material/{id}
DELETE /admin/api/master-material/{id}
```

Required fields:

```text
material_code
material_name
is_active
```

---

# 11. Master Keterangan Endpoints

```text
GET    /admin/master-keterangan
GET    /admin/api/master-keterangan
POST   /admin/api/master-keterangan
PUT    /admin/api/master-keterangan/{id}
DELETE /admin/api/master-keterangan/{id}
```

Default data:

```text
OK
Lot Salah
Size Salah
Material Salah
```

---

# 12. Export Endpoints

## 12.1 GET /admin/export/scan-results/excel

Query:

```text
sto_code
plant_id
location_id
user_id
material_code
lot_number
date_from
date_to
```

Rule:

Export follows active filters.

---

## 12.2 GET /admin/export/scan-results/pdf

Same filters as Excel.

---

# 13. Error Messages

## Invalid QR

```text
Format barcode tidak valid.
```

## Unknown Material

```text
Kode material tidak ditemukan di Master Material.
```

## No Active STO

```text
Tidak ada STO aktif yang tersedia. Silakan hubungi Admin.
```

## Duplicate Barcode

```text
Barcode sudah pernah discan sebelumnya. Tetap simpan?
```

## Unauthorized

```text
Anda tidak memiliki akses.
```

---

# 14. Security Rules

All scanner endpoints:

```text
auth
role:scanner
```

All admin endpoints:

```text
auth
role:admin
```

User scan history and delete must always filter:

```php
where('user_id', auth()->id())
```

---

# 15. Final API Principle

API harus:

```text
Consistent
Validated
Role-protected
Server-side optimized
Ready for AJAX/DataTables
```
