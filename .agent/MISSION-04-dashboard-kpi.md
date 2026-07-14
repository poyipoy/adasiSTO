# MISSION 04 — KPI/Grafik "Lokasi Terkonfirmasi" di Semua Dashboard

> Baca `AGENTS.md` dulu. Mission ini **depends on MISSION-03** — pastikan kolom `is_confirmed` di tabel `locations` sudah ada sebelum mulai.

## Goal
Tambahkan KPI card (atau grafik) di **kedua** dashboard yang sudah ada — admin dan scanner — yang menunjukkan jumlah lokasi yang sudah dikonfirmasi dari total lokasi.

## Prasyarat
- MISSION-03 sudah selesai (kolom `is_confirmed` ada di `locations`, scope `confirmed()`/`unconfirmed()` ada di model)

## Batasan (jangan lakukan)
- **Jangan** bikin query terpisah di luar `Cache::remember` yang sudah ada di masing-masing dashboard — masukkan ke dalam block cache yang sama, ikuti pattern performa yang sudah dibangun.
- Untuk dashboard scanner, **jangan lupa update `OverviewService::scanOverview()`** — kalau cuma diubah di Blade tanpa update service, KPI baru tidak akan ikut ter-refresh otomatis oleh polling 30 detik yang sudah ada.
- Chart Chart.js sifatnya **opsional** — kata user "grafik **atau** kpi card", jadi stat-card saja sudah memenuhi requirement, tidak wajib bikin dua-duanya.

## Langkah Eksekusi

### 1. Dashboard Admin — `Admin\DashboardController::index()`
Tambahkan **di dalam block `Cache::remember` yang sudah ada**:
```php
'total_locations'     => Location::active()->count(),      // hormati filter plant_id kalau ada, sama seperti stat lain
'confirmed_locations' => Location::active()->confirmed()->count(),
```
Tambahkan 1 stat-card baru di `resources/views/admin/dashboard.blade.php`, section "Scan Overview": judul **"Lokasi Terkonfirmasi"**, value format `X / Y` (contoh: `12 / 45`).

*(Opsional)* Tambahkan doughnut chart Chart.js "Status Konfirmasi Lokasi" di sebelah chart "Plant Usage" yang sudah ada (2 slice: confirmed vs belum confirmed).

### 2. Dashboard Scanner — `OverviewService::scanOverview()`
⚠️ Method ini punya live-refresh polling tiap 30 detik lewat `data-card` attribute (lihat `scan/overview.blade.php` bagian JS, fetch ke `route('api.scan.overview')` tiap 30 detik → update elemen `[data-card="key"]`).

1. Tambahkan key baru di return array `scanOverview()` (**masih di dalam** `Cache::remember` yang sudah ada di method itu):
   ```php
   'locations_confirmed' => Location::active()->confirmed()->count(),
   'locations_total'     => Location::active()->count(),
   ```
2. Di `resources/views/scan/overview.blade.php`, tambahkan stat-card baru dengan `data-card="locations_confirmed"` (dan tampilkan totalnya di sebelahnya, atau gabung jadi satu string "X / Y" di value-nya) — ikuti pola persis 4 stat-card yang sudah ada di file itu.

## Acceptance Criteria (Definition of Done)
- [ ] KPI "Lokasi Terkonfirmasi" muncul di dashboard admin dengan format `X / Y`
- [ ] KPI yang sama (atau setara) muncul di dashboard scanner
- [ ] Di dashboard scanner, KPI baru **ikut ter-refresh otomatis** setiap 30 detik tanpa reload halaman (buktikan dengan konfirmasi satu lokasi lewat menu Konfirmasi Rak di tab lain, lalu tunggu ≤30 detik, angka di dashboard scanner harus berubah tanpa refresh manual)
- [ ] Tidak ada query baru di luar block `Cache::remember` yang sudah ada di masing-masing method
- [ ] Kalau filter plant sedang aktif di dashboard admin, angka KPI ikut ter-filter (konsisten dengan stat-card lain di dashboard yang sama)
