# MISSION 02 — Menu "Generate Barcode" (role: admin)

> Baca `AGENTS.md` dulu (terutama bagian "Format Barcode"). Mission ini **depends on MISSION-01** — pastikan tabel `barcode_requests` sudah ada sebelum mulai.

## Goal
Admin menerima request dari menu "Request QR/Barcode" (MISSION-01), mengecek validitas datanya, lalu generate string barcode final + label QR siap cetak.

## Prasyarat
- MISSION-01 sudah selesai (tabel `barcode_requests` ada)

## Batasan (jangan lakukan)
- **Jangan implementasi barcode linear/Code128.** Scanner yang dipakai adalah QR reader (`html5-qrcode`) — kalau digenerate barcode linear, hasilnya tidak akan bisa discan balik. QR-only, ini sudah final.
- Jangan bikin logic baru untuk menentukan `CheckLetter` — default ke `'B'`, beri komentar TODO (lihat `AGENTS.md`).
- Jangan skip validasi server-side hanya karena sudah ada preview di client — validasi ulang di endpoint `generate()`.

## Langkah Eksekusi

### 1. Migration — tambah kolom `qty`
Alter table `barcode_requests`: tambah `qty` (unsignedInteger, nullable) — diisi admin saat proses generate (bukan oleh scanner saat request, karena qty fisik baru pasti saat barang mau dilabel).

### 2. Service — `app/Services/BarcodeGeneratorService.php`
Mirror dari `BarcodeParserService`, method `build(array $data): array` yang:
1. Validasi material ada & `is_active` di `MasterMaterial`
2. Validasi dimensi sesuai shape (>0, field tidak relevan harus 0/null)
3. Zero-pad angka ke jumlah digit yang benar (primary 3 digit, width/length masing-masing 4 digit)
4. Rakit string final sesuai format di `AGENTS.md`
5. Return kontrak mirip `BarcodeParserService::parse()`: `['valid' => bool, 'barcode_material' => string|null, 'errors' => array]`

⚠️ **Wajib tulis test round-trip:** hasil `build()` harus lolos `BarcodeParserService::parse()` dan menghasilkan material_code/shape_code/dimensi yang sama dengan input asal. Ini validasi paling penting di mission ini.

### 3. Controller — `app/Http/Controllers/Admin/GenerateBarcodeController.php`
- `index()` → halaman list request (default filter `status = pending`, tapi bisa filter semua status/plant/material)
- `datatable()` → server-side list `BarcodeRequest`
- `validateData($barcodeRequest)` → panggil `BarcodeGeneratorService::build()`, return preview string barcode + checklist validitas (material ditemukan ✓, dimensi valid ✓, lot number ok ✓) — **tanpa menyimpan apa pun**, dipakai tombol "Cek Validitas"
- `generate($barcodeRequest)` → terima input `qty` dari admin, **validasi ulang di server**, simpan `generated_barcode_material`, `qty`, `status = approved`, `reviewed_by_user_id`, `reviewed_at`, catat `ActivityLogService`, return string barcode final + gambar QR
- `reject($barcodeRequest)` → terima `rejection_reason` (wajib diisi), set `status = rejected`
- `label($barcodeRequest)` → return PDF label cetak

### 4. Routes (masuk ke group `role:admin` + prefix `admin` yang sudah ada)
```php
Route::get('/generate-barcode', [GenerateBarcodeController::class, 'index'])->name('generate-barcode');
Route::get('/api/generate-barcode', [GenerateBarcodeController::class, 'datatable'])->middleware('throttle:datatable')->name('api.generate-barcode');
Route::post('/api/generate-barcode/{barcodeRequest}/validate', [GenerateBarcodeController::class, 'validateData'])->name('api.generate-barcode.validate');
Route::post('/api/generate-barcode/{barcodeRequest}/generate', [GenerateBarcodeController::class, 'generate'])->name('api.generate-barcode.generate');
Route::post('/api/generate-barcode/{barcodeRequest}/reject', [GenerateBarcodeController::class, 'reject'])->name('api.generate-barcode.reject');
Route::get('/generate-barcode/{barcodeRequest}/label', [GenerateBarcodeController::class, 'label'])->name('generate-barcode.label');
```

### 5. Cetak Label (QR only)
Project belum punya library generate QR — yang ada cuma `html5-qrcode` untuk **membaca**, bukan membuat.
1. `composer require endroid/qr-code` — generate gambar QR (SVG/PNG) dari string barcode
2. Render label pakai `barryvdh/laravel-dompdf` (**sudah terpasang**, sudah dipakai untuk export lain) — embed gambar QR + info material/lot/plant/lokasi, layout print-friendly satu label per request
3. Endpoint `label()` di atas return PDF (bisa dibuka tab baru / didownload)

### 6. View — `resources/views/admin/generate-barcode.blade.php`
- DataTable list request: material, jenis, dimensi, lot number, plant, lokasi, requester, status
- Tombol per baris:
  - **Cek Validitas** → modal preview (string barcode + checklist)
  - **Generate** → modal input qty → panggil endpoint generate → tampilkan hasil string + tombol cetak/download PDF
  - **Tolak** → modal input alasan (wajib)
- Pakai `confirmAction()` global untuk konfirmasi generate/reject.

## Acceptance Criteria (Definition of Done)
- [ ] Kolom `qty` berhasil ditambahkan ke `barcode_requests`
- [ ] `BarcodeGeneratorService::build()` menghasilkan string yang **lolos parsing ulang** oleh `BarcodeParserService::parse()` dengan data yang sama — dibuktikan lewat test otomatis, bukan cek manual
- [ ] Tombol "Cek Validitas" tidak mengubah data apa pun di database (read-only preview)
- [ ] Tombol "Generate" gagal dengan pesan jelas kalau data request tidak valid (material inactive, dimensi 0, dll) — validasi server, bukan cuma client
- [ ] Setelah generate, admin bisa download/cetak PDF label berisi QR code yang valid
- [ ] QR code yang dihasilkan, kalau discan pakai scanner yang sudah ada di app ini, berhasil ter-parse dengan benar
- [ ] Reject request wajib mengisi alasan, request yang sudah `approved`/`rejected` tidak bisa digenerate/direject ulang
- [ ] Semua aksi (generate, reject) tercatat di `ActivityLogService`
- [ ] Tidak ada library barcode linear (Code128 dll) yang ditambahkan
