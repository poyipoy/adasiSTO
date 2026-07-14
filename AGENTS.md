# AGENTS.md — adasiSTO (Fastware STO Scan To Office)

Baca file ini SETIAP kali mulai kerja di repo ini, sebelum mengerjakan mission apa pun. Isinya konvensi permanen project — bukan spesifikasi fitur (spesifikasi fitur ada di file `MISSION-*.md` terpisah).

## Ringkasan Project
Laravel 12 app untuk proses Scan To Office (STO) — stock opname material via scan QR/barcode. Ada 2 role: `admin` dan `scanner`. Stack: Blade, Bootstrap 5, DataTables (yajra), SweetAlert2, Chart.js, brand color `#1F5FA6`.

## File Kunci (source of truth — baca dulu sebelum menulis kode)

| File | Kenapa penting |
|---|---|
| `routes/web.php` | Konvensi penamaan route: **tanda hubung**, bukan dot (`admin.master-plant`, `scan.setup`) | 
| `resources/views/components/layouts/app.blade.php` | **Layout utama yang benar-benar dipakai** (`<x-layouts.app>`) |
| `resources/views/layouts/app.blade.php` | ⚠️ LEGACY, tidak dipakai, route-name di dalamnya sudah tidak sinkron. **Jangan edit atau jadikan acuan.** |
| `app/Http/Controllers/Admin/MaterialDoubleController.php` + `resources/views/admin/material-double.blade.php` | Pola controller+view untuk halaman workflow custom (bukan CRUD generic) — ini pattern paling relevan untuk fitur-fitur baru |
| `resources/views/admin/master/generic.blade.php` + `resources/views/admin/master/README.md` | Pola CRUD generic (Master STO/Plant/Material/Keterangan/Location/User). **Jangan pakai pattern ini** kalau fiturnya butuh custom action di luar CRUD murni |
| `app/Services/BarcodeParserService.php` | Format barcode yang berlaku (regex parsing) — lihat juga bagian "Format Barcode" di bawah |
| `app/Models/Location.php`, `Plant.php`, `MasterMaterial.php`, `ScanResult.php`, `ActivityLog.php` | Struktur data & relasi yang sudah ada |
| `app/Http/Middleware/RoleMiddleware.php` | Role hanya `admin` / `scanner`, dicek dari `$user->role` + `$user->is_active` |
| `config/sto.php` | Nilai konfigurasi (limit, rate limit) yang sudah ada |
| `package.json` + `scripts/publish-vendor-assets.js` | **WAJIB** diupdate kalau nambah library JS baru — project ini tidak pakai CDN sama sekali di production |
| `app/Services/OverviewService.php` (`scanOverview()`) + `resources/views/scan/overview.blade.php` | Dashboard scanner punya **live-refresh polling 30 detik** via `data-card` attribute |

## Aturan Wajib

1. Semua halaman baru pakai `<x-layouts.app :title="'...'">`. Jangan bikin layout/CSS baru.
2. Menu baru ditambahkan di sidebar **`resources/views/components/layouts/app.blade.php`** (bukan yang legacy), ikuti struktur `sidebar-section` → `nav-item`, inline SVG icon 24x24 `stroke="currentColor" stroke-width="2"`, dan tambahkan pengecekan `request()->routeIs(...)` yang sesuai.
3. Tabel data pakai **DataTables server-side** (`yajra/laravel-datatables-oracle`), endpoint dengan middleware `throttle:datatable`. Contoh acuan: `MaterialDoubleController::datatable()`.
4. Konfirmasi aksi pakai fungsi global **`confirmAction(message, callback)`** (SweetAlert2) yang sudah ada di layout. Jangan bikin dialog custom baru.
5. Notifikasi sukses/gagal pakai pola toast SweetAlert2 (`showConfirmButton: false`) yang sudah konsisten dipakai di file lain.
6. Endpoint tulis (`POST`/`PUT`/`DELETE`) untuk aksi scanner pakai `throttle:scan-write`. Setiap aksi create/update/confirm/cancel/generate/reject **wajib dicatat ke `ActivityLogService`** (`subject_type`, `old_values`, `new_values` terisi).
7. Role gating: `Route::middleware(['auth','role:admin'])` atau `role:scanner`.
8. Route name, nama tabel, nama kolom → **Bahasa Inggris**. Label yang tampil di UI boleh **Bahasa Indonesia** (contoh existing: menu "Master Keterangan").
9. Migration baru: format nama file `YYYY_MM_DD_HHMMSS_deskripsi_singkat.php`.
10. Nambah library JS baru → `npm install` → tambah entry copy di `scripts/publish-vendor-assets.js` → target `public/vendor/...` → load via `{{ asset('vendor/...') }}`. **Jangan** load dari CDN atau `node_modules` langsung.
11. Warna pakai CSS var yang sudah ada di layout (`--primary` dll). Jangan hardcode warna baru di luar palet yang sudah dipakai.
12. **Satu mission = satu sesi kerja.** Jangan mengerjakan lebih dari satu file `MISSION-*.md` sekaligus dalam satu run, meskipun terlihat berkaitan — supaya tiap perubahan gampang direview/di-rollback terpisah.

## Format Barcode (berlaku di beberapa mission — jangan diubah tanpa konfirmasi)
```
{ShapeCode}{MaterialCode}{Primary:3 digit}-{Secondary:8 digit}{CheckLetter} | {LotNumber} | {Qty}

ShapeCode    = RF (Flat) atau RR (Round)
MaterialCode = 2 karakter alfanumerik, dari master_materials.material_code
Primary      = 3 digit — thickness (RF) atau diameter (RR)
Secondary    = 8 digit — width(4 digit, "0000" kalau RR) + length(4 digit)
CheckLetter  = 1 huruf kapital di akhir. Di semua data & test existing selalu 'B',
               tidak ada logic yang menjelaskan artinya di kode manapun.
               Default-kan 'B', beri komentar TODO, JANGAN menebak logic baru.
```
Referensi implementasi parsing: `App\Services\BarcodeParserService::parse()`.

## Resep Umum Menambah Fitur Baru (dipakai di semua mission)
1. Migration (kalau ada perubahan skema)
2. Model / update model (fillable, casts, relasi, scope)
3. Service (kalau ada logic non-trivial — reuse pattern `BarcodeParserService`)
4. Form Request untuk validasi input
5. Controller (index/datatable/action methods)
6. Routes (masuk ke group role yang sudah ada, ikuti konvensi nama hyphenated)
7. View Blade (form/table, pakai `<x-layouts.app>`)
8. Update sidebar navigasi
9. Update dashboard KPI kalau relevan
10. Test minimal untuk logic yang krusial (contoh: round-trip generate→parse barcode)
11. Cek ulang checklist "Acceptance Criteria" di file mission sebelum menyatakan selesai

## Urutan Mission
```
MISSION-01-request-qr-barcode.md      (standalone, fondasi)
MISSION-02-generate-barcode.md        (depends on: MISSION-01)
MISSION-03-konfirmasi-rak.md          (standalone, independen)
MISSION-04-dashboard-kpi.md           (depends on: MISSION-03)
```
Urutan pengerjaan yang disarankan: 01 → 02 → 03 → 04. Boleh kerjakan 03 lebih dulu kalau mau, karena tidak bergantung ke 01/02.
