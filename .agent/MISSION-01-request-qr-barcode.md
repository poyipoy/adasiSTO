# MISSION 01 — Menu "Request QR/Barcode" (role: scanner)

> Baca `AGENTS.md` dulu sebelum mengerjakan ini. Mission ini standalone, tidak bergantung ke mission lain.

## Goal
User scanner bisa mengajukan permintaan pembuatan label QR/barcode baru untuk material yang belum punya label fisik, lewat menu baru di sidebar scanner.

## Prasyarat
Tidak ada. Bisa langsung dikerjakan.

## Batasan (jangan lakukan)
- Jangan bikin endpoint dropdown lokasi baru — **reuse** `GET /api/locations?plant_id=` (`ScanController::locations`) yang sudah ada.
- Jangan bikin dropdown material query baru selain `MasterMaterial::active()->orderBy('material_name')->get()`.
- Jangan proses/generate string barcode di mission ini — itu tanggung jawab MISSION-02.
- Jangan tambahkan field `qty` — sengaja tidak ada di form request (lihat catatan di bawah).

## Langkah Eksekusi

### 1. Migration — `create_barcode_requests_table`
```
id
user_id                    FK users, cascadeOnDelete           -- requester
sto_code_id                FK sto_codes, nullable, nullOnDelete
plant_id                   FK plants, cascadeOnDelete
location_id                FK locations, cascadeOnDelete
material_code               string   -- denormalized, sama pola dgn scan_results
material_name               string
shape_code                  string(10)   -- RF / RR
shape_name                  string(50)   -- Flat / Round
thickness                   unsignedInteger nullable
width                        unsignedInteger nullable
diameter                     unsignedInteger nullable
length                        unsignedInteger nullable
lot_number                    string
status                        string default 'pending'   -- pending | approved | rejected
rejection_reason             string nullable
generated_barcode_material    string nullable   -- diisi MISSION-02 setelah digenerate
reviewed_by_user_id           FK users nullable
reviewed_at                    timestamp nullable
timestamps
```
Index: `user_id`, `plant_id`, `location_id`, `status`, `material_code`.

Field `thickness/width/diameter/length` diisi sesuai `shape_code` — ikuti persis logika `scan_results` (RF pakai thickness+width+length, RR pakai diameter+length).

### 2. Model — `app/Models/BarcodeRequest.php`
- Relasi: `user()`, `plant()`, `location()`, `reviewedBy()`
- Scope: `scopePending()`
- Tiru pola `getSizeAttribute()` dari `App\Models\ScanResult` untuk format tampilan dimensi (konsistensi)

### 3. Form Request — `StoreBarcodeRequestRequest`
Validasi:
- `material_code` exists & `is_active=true` di `master_materials`
- `shape_code` in:RF,RR
- Dimensi wajib >0 sesuai shape (thickness+width+length untuk RF, diameter+length untuk RR) — field yang tidak relevan harus kosong/null
- `lot_number` required string
- `plant_id` exists & `is_active=true`
- `location_id` exists **dan benar-benar milik `plant_id` tersebut** (validasi relasi, bukan cuma exists)

### 4. Controller — `app/Http/Controllers/BarcodeRequestController.php`
(sejajar dengan `ScanController`, **bukan** di bawah `Admin/`)
- `index()` → halaman form + tabel riwayat request milik user login
- `store(StoreBarcodeRequestRequest $request)` → simpan request baru, `status = pending`
- `datatable()` → list request **milik user login saja** (pola scope-by-user seperti di `ScanResult`)
- `destroy($id)` → scanner boleh cancel request miliknya sendiri **hanya kalau `status = pending`** (403 kalau bukan pending atau bukan miliknya)

### 5. Routes (`routes/web.php`, di dalam group `role:scanner` yang sudah ada)
```php
Route::get('/scan/barcode-request', [BarcodeRequestController::class, 'index'])->name('scan.barcode-request');
Route::get('/api/barcode-request', [BarcodeRequestController::class, 'datatable'])->middleware('throttle:datatable')->name('api.barcode-request');
Route::post('/api/barcode-request', [BarcodeRequestController::class, 'store'])->middleware('throttle:scan-write')->name('api.barcode-request.store');
Route::delete('/api/barcode-request/{id}', [BarcodeRequestController::class, 'destroy'])->middleware('throttle:scan-write')->name('api.barcode-request.destroy');
```

### 6. View — `resources/views/scan/barcode-request.blade.php`
- Card form di atas (gaya mirip `scan/setup.blade.php`) + DataTable riwayat request di bawah
- Field form:
  - **Nama Material** → `<select>` dari `MasterMaterial::active()`
  - **Jenis** → select/radio Flat (`RF`) / Round (`RR`)
  - **Dimensi** → field muncul/hilang dinamis sesuai Jenis — tiru JS toggle di `attachInlineEvents()` pada `resources/views/admin/scan-results.blade.php` (disable + clear field yang tidak relevan saat shape berubah)
  - **Lot Number** → text input
  - **Plant** → select `Plant::active()`
  - **Lokasi** → select, reload options via AJAX tiap Plant berubah (pola sama seperti `scan/setup.blade.php`)
- Tombol cancel di baris riwayat (hanya tampil kalau `status = pending`), pakai `confirmAction()` global

### 7. Sidebar
Tambah menu baru di section "Scan Material" (role scanner) di `resources/views/components/layouts/app.blade.php`, link ke `route('scan.barcode-request')`, label **"Request QR/Barcode"**.

## Acceptance Criteria (Definition of Done)
- [ ] Migration jalan tanpa error, tabel `barcode_requests` sesuai skema di atas
- [ ] User scanner bisa submit request baru, muncul di riwayat dengan status "pending"
- [ ] Dropdown Dimensi berubah otomatis sesuai Jenis (Flat → thickness/width/length, Round → diameter/length), field yang tidak relevan ter-disable/clear
- [ ] Dropdown Lokasi reload otomatis saat Plant diganti (reuse endpoint existing, bukan endpoint baru)
- [ ] User scanner **tidak bisa** melihat/mengubah request milik user lain
- [ ] User scanner bisa cancel request miliknya sendiri selama masih `pending`; setelah bukan pending, tombol cancel hilang/nonaktif
- [ ] Menu baru muncul di sidebar scanner dengan icon konsisten gaya menu lain
- [ ] Tidak ada endpoint duplikat untuk lokasi/material dropdown
