## TASK: Review Perubahan Local vs Prod — adasiSTO

### Context
Saya sedang mengembangkan fitur baru di `adasiSTO` secara lokal, dan sudah
banyak mengedit file dibanding versi yang sedang jalan di prod (beberapa hari
lalu). Saya butuh review menyeluruh sebelum deploy ulang, supaya:
1. Tidak ada perubahan yang tidak sengaja (unintended) ikut kebawa
2. Tidak melanggar konvensi project (lihat AGENTS.md di bawah)
3. Saya paham betul apa saja yang berubah per file, dikelompokkan biar mudah direview

### File yang dibandingkan (unified diff terlampir, format `prod/...` vs `local/...`)
1. `app/Http/Controllers/Admin/DashboardController.php` (+53/-45)
2. `app/Http/Controllers/Admin/MasterController.php` (+32/-11)
3. `app/Http/Controllers/ScanController.php` (+12/-7)
4. `app/Models/Location.php` (+31/-3)
5. `app/Models/ScanResult.php` (+17/-9)
6. `app/Services/ScanService.php` (+9/-5)
7. `resources/views/admin/scan-results.blade.php` (+5/-78)
8. `resources/views/components/layouts/app.blade.php` (+84/-26)  ← layout AKTIF
9. `resources/views/layouts/app.blade.php` (+15/-46)  ← ⚠️ ini LEGACY, seharusnya tidak dipakai
10. `resources/views/scan/results.blade.php` (+22/-4)
11. `resources/views/scan/scanner.blade.php` (+176/-102)  ← perubahan terbesar
12. `routes/web.php` (+47/-0, murni penambahan)

### Yang saya butuhkan, per file:
1. **Ringkasan perubahan** dalam bahasa manusia (per fungsi/method/blok),
   bukan penjelasan baris-per-baris mentah
2. Kelompokkan menjadi:
   - ✅ **Perubahan yang jelas** — fitur baru / bug fix yang disengaja
   - ⚠️ **Berpotensi breaking** — ubah signature function, ubah nama variabel
     yang dipakai file lain, ubah struktur data/kolom, ubah route name
   - ❓ **Perlu saya konfirmasi** — perubahan yang maksudnya ambigu / kelihatan
     tidak sengaja
3. Cek khusus untuk `resources/views/admin/scan-results.blade.php` (-78 baris):
   apakah ada bagian penting yang **terhapus tidak sengaja**, bukan disederhanakan?
4. Cek khusus untuk `resources/views/layouts/app.blade.php` — ini file LEGACY
   menurut konvensi project. Kenapa ikut berubah? Apakah perubahan di sini
   seharusnya dipindah ke `components/layouts/app.blade.php` (layout aktif) saja?
5. Cek konsistensi dengan aturan di `AGENTS.md` (lihat lampiran), terutama:
   - Format barcode di `BarcodeParserService` tidak boleh berubah tanpa konfirmasi
   - Semua endpoint tulis harus tercatat ke `ActivityLogService`
   - Tabel data harus tetap pakai DataTables server-side dengan `throttle:datatable`
   - Warna harus tetap pakai CSS var yang ada, bukan hardcode baru
   - Route name pakai tanda hubung, bukan dot notation

### Format output yang saya mau
Heading per file → sub-heading per kategori (✅/⚠️/❓). Jangan tampilkan ulang
diff mentahnya, cukup rujuk baris/fungsi yang dimaksud.
