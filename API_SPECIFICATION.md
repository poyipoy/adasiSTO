# API_SPECIFICATION.md - STO Scan To Office

Dokumen ini mendeskripsikan endpoint aktif. Semua endpoint selain login/health membutuhkan session auth Laravel dan CSRF token untuk method tulis.

## 1. Konvensi Response

### 1.1 JSON sukses

```json
{
  "success": true,
  "message": "Operasi berhasil.",
  "data": {}
}
```

### 1.2 Validation error

HTTP `422` dengan `message` dan `errors` Laravel.

### 1.3 Authorization dan throttle

- Tidak terautentikasi: `401` untuk JSON atau redirect login untuk browser.
- Tidak memiliki capability: `403`.
- Terlalu banyak request: `429`.
- Data tidak ditemukan: `404`.

### 1.4 DataTables server-side

Endpoint tabel menerima `draw`, `start`, `length`, `search[value]`, `order`, dan filter halaman. Response:

```json
{
  "draw": 1,
  "recordsTotal": 100,
  "recordsFiltered": 10,
  "data": []
}
```

`start` di-clamp minimum `0`; `length` di-clamp ke `config('sto.datatable_max_length')`.

## 2. Authentication dan Health

| Method | Path | Akses | Fungsi |
|---|---|---|---|
| GET | `/health` | Public | Health check aman |
| GET | `/login` | Guest | Form login |
| POST | `/login` | Guest | Login username/password; throttle login |
| POST | `/logout` | Auth | Logout dan invalidasi session |

`GET /health` hanya memeriksa application bootstrap, koneksi database, dan akses storage. Queue tidak menjadi dependency health karena runtime aktif tidak mengoperasikan queue worker.

## 3. Scanner

| Method | Path | Fungsi |
|---|---|---|
| GET | `/scan/overview` | Overview scanner |
| GET | `/api/scan/overview` | Data overview realtime |
| GET | `/scan/setup` | Setup Active STO, Plant, Location |
| POST | `/scan/setup` | Simpan context scan ke session |
| GET | `/scan/scanner` | Halaman scanner |
| GET | `/scan/history` | Scan History |
| GET | `/scan/material-summary` | Material Summary |
| GET | `/api/scan/recent` | Recent scan Today, maksimum 50/page |
| GET | `/api/scan/history` | History server-side milik scanner |
| GET | `/api/scan/material-summary` | Summary; global untuk validator |
| POST | `/api/scan/preview` | Parse QR tanpa menyimpan |
| POST | `/api/scan/check-duplicate` | Cek duplicate Active STO/Plant/Location |
| POST | `/api/scan/store` | Simpan scan |
| DELETE | `/api/scan/{id}` | Hard delete scan milik scanner + audit |

Payload store scan:

```json
{
  "qr": "RF1H059-00960099B|LOT001|1",
  "plant_id": 1,
  "location_id": 2,
  "scan_source": "scanner_gun",
  "force_save": false
}
```

Duplicate tanpa `force_save=true` mengembalikan HTTP `409`:

```json
{
  "success": false,
  "duplicate": true,
  "message": "Barcode ... sudah pernah discan ... Tetap simpan?"
}
```

## 4. Location Scanner

| Method | Path | Fungsi |
|---|---|---|
| GET | `/api/locations?plant_id={id}` | List Location aktif pada Plant |
| POST | `/api/locations` | Buat Location; actor disimpan |
| DELETE | `/api/locations/{id}` | Hapus Location buatan sendiri yang belum digunakan |

Delete Location mengembalikan HTTP `422` dengan `{ success: false, message }` jika Location sudah digunakan oleh scan pada STO mana pun, Request Barcode, atau workflow Rack Confirmation yang memiliki status/history operasional. Data tersebut harus dinonaktifkan, bukan di-hard delete. Placeholder Rack Confirmation `pending` yang belum pernah diproses dibersihkan otomatis saat Location yang benar-benar belum digunakan dihapus.

Create payload:

```json
{
  "plant_id": 1,
  "name": "CT01"
}
```

Unique rule: `(plant_id, name)`.

## 5. Barcode Request

Area ini hanya role scanner.

| Method | Path | Fungsi |
|---|---|---|
| GET | `/scan/barcode-request` | Halaman Request QR/Barcode |
| GET | `/api/barcode-request` | DataTable request |
| GET | `/api/barcode-request/suggestions` | Saran dimensi material |
| POST | `/api/barcode-request` | Buat request `pending` |
| DELETE | `/api/barcode-request/{id}` | Batalkan request pending milik sendiri |

Payload create:

```json
{
  "material_code": "1H",
  "shape_code": "RF",
  "thickness": 59,
  "width": 96,
  "diameter": null,
  "length": 99,
  "lot_number": "LOT001",
  "plant_id": 1,
  "location_id": 2,
  "print_count": 50
}
```

Backend selalu menyimpan `qty=1`.

## 6. Generate Barcode

Akses: admin dan scanner validator melalui `generate-barcode-access`. Add Barcode tetap admin-only.

| Method | Path | Fungsi |
|---|---|---|
| GET | `/admin/generate-barcode` | Halaman Generate Barcode |
| GET | `/admin/api/generate-barcode` | DataTable request |
| GET | `/admin/api/generate-barcode/locations?plant_id={id}` | Seluruh Location aktif untuk Plant pada form Add Barcode |
| POST | `/admin/api/generate-barcode` | Add Barcode `pending`, admin-only |
| POST | `/admin/api/generate-barcode/{barcodeRequest}/validate` | Preview validasi |
| POST | `/admin/api/generate-barcode/{barcodeRequest}/generate` | `pending -> approved` |
| POST | `/admin/api/generate-barcode/{barcodeRequest}/reject` | Selalu `422`; action legacy dinonaktifkan |
| POST | `/admin/api/generate-barcode/batch-generate` | Generate beberapa request secara atomic |
| GET | `/admin/generate-barcode/{barcodeRequest}/label` | Single print/reprint PDF |
| POST | `/admin/generate-barcode/label-bulk` | Bulk PDF |

Semua PDF synchronous. Status `printed` dan ActivityLog disimpan setelah render PDF berhasil. QR payload selalu `{generated_barcode_material}|{lot_number}|1`.

## 7. Material Double

Akses: admin dan scanner validator melalui `material-double-access`.

| Method | Path | Fungsi |
|---|---|---|
| GET | `/admin/material-double` | Halaman duplicate group |
| GET | `/admin/api/material-double` | DataTable group duplicate |
| GET | `/admin/api/material-double/detail` | Detail row satu group |
| GET | `/admin/api/material-double/locations?plant_id={id}` | Semua Location aktif untuk Plant |
| POST | `/admin/api/material-double/validate` | Tandai group Valid |
| POST | `/admin/api/material-double/scan` | Replacement scan pada Plant/Location group |
| DELETE | `/admin/api/material-double/delete-selected` | Partial hard delete atomic |
| GET | `/admin/api/material-double/export/excel` | Export XLSX synchronous |

Group payload canonical:

```json
{
  "barcode_material": "RF1H059-00960099B",
  "plant_id": 1,
  "location_id": 2,
  "ids": [10, 11]
}
```

`ids` hanya untuk delete. Delete harus menyisakan minimal satu row, membuat audit untuk setiap row, menghapus validasi lama, dan tidak membuat status Valid baru.

Replacement scan menambah `qr` dan `force_save`; response duplicate mengikuti HTTP `409` scanner.

## 8. Rack Confirmation

Akses halaman/data: admin dan seluruh scanner melalui `rack-confirmation-access`.

| Method | Path | Akses action | Transition |
|---|---|---|---|
| GET | `/admin/rack-confirmation` | Semua | Halaman |
| GET | `/admin/api/rack-confirmation` | Semua | DataTable Active STO |
| POST | `/admin/api/rack-confirmation/{location}/finish` | Scanner/validator | `pending -> finished` |
| POST | `/admin/api/rack-confirmation/{location}/validate` | Validator | `finished -> validated` |
| POST | `/admin/api/rack-confirmation/{location}/confirm` | Admin | `validated -> confirmed` |
| POST | `/admin/api/rack-confirmation/{location}/cancel` | Admin | Non-pending -> `pending` |
| GET | `/admin/rack-confirmation/export` | Admin | CSV synchronous |

`cancel` membutuhkan `note` minimal 5 karakter. Seluruh transition menambah history dan ActivityLog.

## 9. History Delete Barcode

| Method | Path | Fungsi |
|---|---|---|
| GET | `/history/delete-barcode` | Halaman read-only |
| GET | `/api/history/delete-barcode` | DataTable server-side |
| GET | `/api/history/delete-barcode/{history}` | Detail snapshot |

Admin melihat seluruh history. Scanner/validator melihat row yang dihapus olehnya atau scan asli miliknya. Tidak ada endpoint create, edit, delete, restore, atau bulk mutation untuk history.

## 10. Admin

Endpoint admin-only utama:

```text
GET    /admin/dashboard
GET    /admin/api/dashboard/latest-scan
GET    /admin/scan-results
GET    /admin/api/scan-results
POST   /admin/api/scan-results
PUT    /admin/api/scan-results/{id}
DELETE /admin/api/scan-results/{id}
GET    /admin/material-summary
GET    /admin/api/material-summary
GET    /admin/master-sto
GET    /admin/master-plant
GET    /admin/master-material
GET    /admin/master-keterangan
GET    /admin/master-location
GET    /admin/users
```

CRUD master mengikuti pola `GET page`, `GET /api/...` DataTable, lalu `POST`, `PUT`, dan `DELETE` untuk resource terkait.

## 11. Export Runtime

Endpoint export aktif tidak membuat queue job:

```text
GET /admin/export/scan-results/excel
GET /admin/export/scan-results/pdf
GET /admin/api/material-double/export/excel
GET /admin/rack-confirmation/export
```

Response file dibuat synchronous dan request browser menunggu sampai selesai. Project belum menjalankan queue worker atau polling status export.
