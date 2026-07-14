# MISSION 03 — Menu "Konfirmasi Rak" (role: admin)

> Baca `AGENTS.md` dulu. Mission ini **standalone**, tidak bergantung ke MISSION-01/02, tapi **MISSION-04 bergantung ke mission ini** (butuh kolom `is_confirmed`).

## Goal
Admin melakukan **rekonsiliasi fisik vs sistem**: datang langsung ke rak, bandingkan barang + label QR yang ada di rak dengan apa yang tercatat di sistem, lalu konfirmasi (atau batalkan konfirmasi) lokasi tersebut.

## Konteks Penting
Ini bukan sekadar fitur "tampilkan angka lalu klik centang". Field **"Total Barcode"** di layar berfungsi sebagai **acuan buat admin di lapangan** — "menurut sistem, seharusnya ada segini barcode di rak ini, cek dong fisiknya beneran segini". Kalau nanti ada selisih pas cek fisik, admin butuh cara mencatatnya (bukan cuma diam-diam batal konfirmasi tanpa jejak).

Field yang diminta user (**lokasi, plant, total barcode**) sudah persis mengikuti relasi `Location belongsTo Plant` + `Location` terhubung ke `ScanResult` via `location_id` yang **sudah ada** — jadi mission ini adalah **extend `Location`**, bukan bikin entitas baru.

## Prasyarat
Tidak ada. Bisa dikerjakan kapan saja, termasuk sebelum MISSION-01/02.

## Batasan (jangan lakukan)
- **Jangan** hitung "Total Barcode" pakai `COUNT(*)` mentah dari `scan_results` — harus distinct + filter valid (lihat langkah 3).
- **Jangan** pakai `DB::table()` mentah untuk query `ScanResult` — pakai Eloquent query builder supaya global scope `active_sto` otomatis kepakai.
- **Jangan** biarkan tombol "Batalkan Konfirmasi" submit tanpa alasan — catatan wajib diisi.

## Langkah Eksekusi

### 1. Migration — `add_confirmation_to_locations_table`
Tambahkan ke tabel `locations`:
```
is_confirmed         boolean default false
confirmed_by_user_id  FK users, nullable, nullOnDelete
confirmed_at          timestamp nullable
confirmation_note     string nullable, 500   -- catatan admin: kondisi cek fisik, atau alasan cancel/selisih
```

### 2. Update Model — `app/Models/Location.php`
- Tambahkan `is_confirmed`, `confirmed_by_user_id`, `confirmed_at`, `confirmation_note` ke `$fillable`
- Casts: `is_confirmed` → boolean, `confirmed_at` → datetime
- Relasi baru: `confirmedBy(): BelongsTo` → `User::class, 'confirmed_by_user_id'`
- Scope: `scopeConfirmed()`, `scopeUnconfirmed()`

### 3. Controller — `app/Http/Controllers/Admin/RackConfirmationController.php`
- `index()` → halaman list lokasi
- `datatable()` → query `Location` join `Plant`, dengan subquery **"Total Barcode"** dari `ScanResult` per `location_id`:
  - **`COUNT(DISTINCT barcode_material)`** — bukan `COUNT(*)`, biar barang yang discan berkali-kali (misal validasi ulang) tetap dihitung 1
  - **`WHERE keterangan = 'OK'`** — barang yang gagal validasi/invalid tidak dianggap "ada di rak"
  - Eloquent query builder, bukan `DB::table` mentah, supaya global scope `active_sto` otomatis kepakai

  Contoh pendekatan:
  ```php
  ScanResult::query()
      ->select('location_id')
      ->selectRaw('COUNT(DISTINCT barcode_material) as total_barcode')
      ->where('keterangan', 'OK')
      ->groupBy('location_id');
  ```
- `confirm($location)` → terima `note` opsional (hasil cek fisik), set `is_confirmed = true`, `confirmed_by_user_id = auth()->id()`, `confirmed_at = now()`, `confirmation_note = $note`, catat `ActivityLogService`
- `cancel($location)` → terima `note` **wajib diisi** (alasan/selisih), set `is_confirmed = false`, `confirmed_by_user_id = null`, `confirmed_at = null`, `confirmation_note = $note`, catat `ActivityLogService` dengan `old_values` terisi (biar histori konfirmasi sebelumnya tidak hilang dari audit trail)

### 4. Routes (masuk ke group `role:admin` + prefix `admin` yang sudah ada)
```php
Route::get('/rack-confirmation', [RackConfirmationController::class, 'index'])->name('rack-confirmation');
Route::get('/api/rack-confirmation', [RackConfirmationController::class, 'datatable'])->middleware('throttle:datatable')->name('api.rack-confirmation');
Route::post('/api/rack-confirmation/{location}/confirm', [RackConfirmationController::class, 'confirm'])->name('api.rack-confirmation.confirm');
Route::post('/api/rack-confirmation/{location}/cancel', [RackConfirmationController::class, 'cancel'])->name('api.rack-confirmation.cancel');
```

### 5. View — `resources/views/admin/rack-confirmation.blade.php`
- DataTable kolom: Lokasi, Plant, Total Barcode (angka acuan sistem), Status Konfirmasi (badge), Dikonfirmasi Oleh, Tanggal Konfirmasi, Catatan
- Tombol per baris:
  - **Konfirmasi** (kalau belum confirmed) → modal tampilkan Total Barcode sebagai acuan ("Sistem mencatat **N** barcode di lokasi ini — pastikan sudah dicek fisik"), input catatan opsional, submit via `confirmAction()`
  - **Batalkan Konfirmasi** (kalau sudah confirmed) → modal input catatan/alasan **wajib**, submit via `confirmAction()`
- Menu baru di sidebar section "Master Data" (role admin), label **"Konfirmasi Rak"**.

## Acceptance Criteria (Definition of Done)
- [ ] Migration jalan tanpa error, 4 kolom baru ada di `locations`
- [ ] Angka "Total Barcode" = distinct `barcode_material` dengan `keterangan='OK'`, scoped ke STO aktif — bukan raw count
- [ ] Admin bisa konfirmasi lokasi (catatan opsional) dan hasilnya langsung terlihat di badge status
- [ ] Admin **tidak bisa** membatalkan konfirmasi tanpa mengisi catatan/alasan
- [ ] Riwayat konfirmasi sebelumnya (siapa, kapan) tetap tercatat di `ActivityLogService` walau sudah dibatalkan
- [ ] Menu baru muncul di sidebar admin
- [ ] Query datatable tidak memakai `DB::table()` mentah untuk `scan_results` (harus tetap tunduk ke global scope STO aktif)
