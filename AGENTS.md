# AGENTS.md - STO Scan To Office

Baca dokumen ini sebelum mengubah repository. File ini berisi aturan engineering dan business invariant yang berlaku lintas fitur. Detail visual berada di `DESIGN.md`, struktur data di `DATABASE.md`, kontrak HTTP di `API_SPECIFICATION.md`, dan hak akses di `AUTHORIZATION_MATRIX.md`.

## 1. Ringkasan Sistem

STO adalah aplikasi Laravel 12 + MySQL untuk stock opname material melalui QR/barcode. Runtime UI menggunakan Blade, DataTables server-side, SweetAlert2, Chart.js, `html5-qrcode`, dan asset self-hosted.

Role hanya:

- `admin`
- `scanner`

Validator bukan role baru. Validator adalah `scanner` dengan `is_validator=true`. Admin selalu memiliki capability validator.

## 2. Source of Truth

| File | Fungsi |
|---|---|
| `routes/web.php` | Route dan middleware aktif |
| `DATABASE.md` | Tabel, relasi, index, dan lifecycle data |
| `API_SPECIFICATION.md` | Kontrak endpoint aktif |
| `AUTHORIZATION_MATRIX.md` | Capability tiap role |
| `DESIGN.md` | Pedoman canonical UI/UX |
| `resources/views/components/layouts/app.blade.php` | Application shell dan sidebar aktif |
| `app/Services/BarcodeParserService.php` | Parser QR final |
| `app/Services/BarcodeRequestService.php` | Request, generate, dan print barcode |
| `app/Services/RackConfirmationService.php` | State machine Rack Confirmation |
| `app/Services/ScanService.php` | Store/update/delete scan dan audit |
| `app/Services/MaterialDoubleQueryService.php` | Query group duplicate bersama |
| `app/Services/ShapeFormatter.php` | Nama shape canonical |

`resources/views/layouts/app.blade.php` adalah legacy dan bukan acuan application shell.

## 3. Business Invariants

### 3.1 Active STO

- Hanya satu `sto_codes` yang aktif.
- Scanner tidak memilih STO. Semua scan dan workflow operasional memakai STO aktif.
- Jika tidak ada STO aktif, scan baru ditolak dengan pesan canonical.
- Query `ScanResult` memakai global scope Active STO. Query audit/historical yang memang perlu melewati scope harus menyebut `withoutGlobalScope('active_sto')` secara eksplisit.

### 3.2 QR dan shape

Payload QR final:

```text
{barcode_material}|{lot_number}|{qty}
```

Format `barcode_material`:

```text
{ShapeCode}{MaterialCode}{Primary:3}-{Secondary:8}{CheckLetter}
```

| Shape | Nama | Primary | Secondary |
|---|---|---|---|
| `RF` | `FLAT` | thickness | width 4 digit + length 4 digit |
| `RR` | `ROUND` | diameter | `0000` + length 4 digit |
| `RH` | `HOLLOW` | thickness | width 4 digit + length 4 digit |

- `MaterialCode` harus ditemukan dan aktif di `master_materials`.
- `CheckLetter` tetap `B` sampai ada requirement resmi yang menggantinya.
- Data baru menyimpan nama shape uppercase melalui `ShapeFormatter`.
- Data legacy dengan casing berbeda tetap ditampilkan uppercase tanpa mass update.
- Barcode hasil Generate selalu memakai qty payload `1`. `print_count` adalah jumlah label dan tidak pernah masuk payload QR.

### 3.3 Duplicate scan

- Duplicate adalah barcode yang sama pada Plant dan Location yang sama di Active STO.
- Duplicate boleh disimpan sebagai row baru hanya setelah konfirmasi eksplisit `force_save=true`.
- Jangan menggabungkan row, menambah qty row lama, atau auto-submit duplicate karena barcode dipindai kembali.
- Scan baru pada group yang telah divalidasi mengembalikan group Active STO tersebut ke `Need Check`.

### 3.4 Barcode Request dan print

State aktif:

```text
pending -> approved -> printed
```

- `rejected` legacy tetap dapat dibaca, tetapi aksi Reject baru tidak tersedia.
- Request Barcode dan Add Barcode menerima `print_count`; `qty` selalu `1`.
- Add Barcode hanya admin dan selalu membuat row `pending`.
- Generate Barcode dapat diakses admin dan scanner validator.
- Print pertama mengubah `approved` menjadi `printed`; reprint mempertahankan `printed`.
- Single, bulk, dan reprint wajib dicatat di `activity_logs` dengan request IDs, tipe print, label per request, total label, dan batch UUID.
- PDF dirender penuh sebelum status print atau audit disimpan.
- QR image dibuat sekali per request dan direuse sebanyak `print_count`.

### 3.5 Rack Confirmation

State berlaku per `(sto_code_id, location_id)`:

```text
pending -> finished -> validated -> confirmed
```

- Scanner: `pending -> finished`.
- Scanner validator: dapat finish dan `finished -> validated`.
- Admin: `validated -> confirmed`.
- Admin dapat cancel status non-pending kembali ke `pending`; alasan minimal 5 karakter wajib diisi.
- Seluruh transisi memakai transaction, row lock, `rack_confirmation_histories`, dan strict ActivityLog.
- Row yang belum ada diperlakukan sebagai `pending`.
- KPI dan export menghitung `COUNT(*)` scan dengan `keterangan=OK` pada Active STO, bukan `SUM(qty)`.

### 3.6 Material Double

- Group canonical mengikuti query `MaterialDoubleQueryService` dan selalu menggunakan `location_id` yang cocok dengan `plant_id`.
- Tombol Valid membuat atau memperbarui `material_double_validations`.
- Tidak Valid membuka detail dan memungkinkan partial hard delete.
- Delete Selected harus menyisakan minimal satu `scan_results`.
- Delete Selected tidak membuat status Valid. Validasi lama pada group dibersihkan dan status kembali `Need Check` sampai tombol Valid ditekan.
- Seluruh delete satu request bersifat atomic.

### 3.7 Delete scan

Semua hard delete harus melalui `ScanService`:

- Scanner History: source `scan_history`.
- All Scan Results: source `admin_scan_results`.
- Material Double: source `material_double`.

Urutan wajib dalam satu transaction:

1. Simpan snapshot ke `barcode_delete_histories`.
2. Simpan `scan_result_logs` action `deleted`.
3. Simpan strict `activity_logs` action `scan.deleted`.
4. Hard delete `scan_results`.

Kegagalan salah satu audit harus me-rollback seluruh delete. `barcode_delete_histories` dan `rack_confirmation_histories` bersifat append-only.

### 3.8 Material Summary

- Admin dan scanner memakai query service yang sama.
- `Qty Total = SUM(qty)`.
- `Total Scan Count = COUNT(*)`.
- Group mencakup barcode, material, `shape_code`, dimensi, Plant, dan Location agar variasi casing legacy tidak memecah data.
- Scanner biasa melihat data miliknya; scanner validator melihat data global Active STO.

## 4. Authorization

- Gunakan role middleware untuk area role murni.
- Gunakan capability middleware untuk fitur lintas role:
  - `material-double-access`
  - `generate-barcode-access`
  - `rack-confirmation-access`
- UI visibility bukan authorization. Setiap write endpoint tetap memeriksa capability backend.
- Add Barcode tetap memakai `role:admin` walaupun berada di group Generate Barcode bersama.
- Scanner hanya dapat menghapus scan miliknya, kecuali scanner validator memakai action Material Double yang diautorisasi policy.
- History Delete Barcode: admin melihat semua; scanner melihat history yang dilakukan olehnya atau scan asli miliknya.

## 5. Laravel Engineering Rules

1. Controller tipis; logic transaksi dan lifecycle berada di service.
2. Validasi input memakai Form Request jika payload non-trivial.
3. Authorization memakai middleware, policy, atau gate server-side.
4. Proses multi-write memakai `DB::transaction()` dan row lock bila ada race condition.
5. Create/update/delete/status transition penting wajib tercatat di `ActivityLogService`.
6. Audit yang menentukan integritas delete/status memakai `recordOrFail()`.
7. Jangan memakai `ScanResult::all()` atau memuat tabel besar ke browser.
8. DataTables harus server-side, clamp `start >= 0`, dan clamp `length` ke `config('sto.datatable_max_length')`.
9. Filter tanggal memakai timestamp range start/end-of-day agar index-friendly.
10. Pencarian `LIKE` harus escape `%` dan `_` bila input user dipakai sebagai literal.
11. Pertahankan route name dan URL existing kecuali requirement menyatakan perubahan kontrak.
12. Migration production harus additive/kompatibel dan tidak mengubah kolom nullable kembali menjadi `NOT NULL` pada rollback.

## 6. UI dan Asset

- Semua halaman memakai `<x-layouts.app>` dan aturan `DESIGN.md`.
- Tambahkan menu hanya di `resources/views/components/layouts/app.blade.php`.
- Halaman workspace harus bekerja sebagai full page dan `html.is-iframe`.
- Asset production harus self-hosted melalui `public/vendor`; jangan menambah CDN.
- Library baru wajib ditambahkan ke `package.json` dan `scripts/publish-vendor-assets.js`.
- Gunakan satu loading indicator per konteks.
- Action destructive memakai SweetAlert2 confirmation dan backend audit.

## 7. Export dan Runtime

- Export Scan Results, Material Double, dan Rack Confirmation aktif berjalan synchronous dalam request HTTP.
- Project belum mengoperasikan queue worker.
- Route/UI aktif tidak boleh mengklaim status queued, polling worker, atau background completion.
- Class job dan `ExportRequest` yang masih tersedia adalah infrastructure nonaktif dan bukan kontrak runtime.
- Gunakan `QUEUE_CONNECTION=sync` sampai worker, retry, monitoring, dan deployment queue diterapkan secara terpisah.

## 8. Testing dan Verifikasi

Perubahan behavior wajib memiliki test MySQL dengan `RefreshDatabase`. Minimal verifikasi:

```bash
php artisan migrate --force
php artisan test
vendor/bin/pint --test
php artisan route:list --except-vendor
php artisan view:cache
php artisan optimize:clear
git diff --check
```

Untuk perubahan UI, verifikasi manual pada full page dan tab iframe, desktop/mobile, state kosong/loading/error, serta role admin/scanner/scanner-validator yang relevan.
