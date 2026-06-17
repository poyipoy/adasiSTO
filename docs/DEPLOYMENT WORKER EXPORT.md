# STO Application Deployment Guide

Dokumen ini berisi panduan teknis lengkap untuk melakukan instalasi dan _deployment_ aplikasi STO (Scan To Office) ke server Linux (Ubuntu/Debian) untuk fase _Production_. Fokus utama dari dokumen ini adalah **menyiapkan konfigurasi Export dan Queue Worker** agar berjalan 24/7 di balik layar.

---

## 1. Persyaratan Server (Server Requirements)

Sebelum memulai instalasi, pastikan server (VPS / VM) telah ter-install:
- **OS**: Ubuntu 20.04 / 22.04 (atau distro berbasis Debian lainnya)
- **Web Server**: Nginx atau Apache
- **PHP**: Versi 8.2 atau yang lebih baru
- **Database**: MySQL 8.0
- **Process Monitor**: Supervisor

Ekstensi PHP wajib (`php-extensions`):
`bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`, `gd`.

---

## 2. Persiapan Project di Server

1. **Clone Repository / Upload Source Code**
   Pindahkan _source code_ STO ke direktori root web server, umumnya berada di `/var/www/adasi_sto`.

2. **Pengaturan Direktori dan Permissions**
   Pastikan folder-folder Laravel memiliki hak akses penulisan (_writable_) oleh _web server_ (biasanya user `www-data`):
   ```bash
   cd /var/www/adasi_sto
   sudo chown -R $USER:www-data storage
   sudo chown -R $USER:www-data bootstrap/cache
   sudo chmod -R 775 storage
   sudo chmod -R 775 bootstrap/cache
   ```

3. **Instalasi Dependencies (Composer)**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

4. **Konfigurasi Environment (.env)**
   Salin `.env.example` menjadi `.env` lalu atur _database_, URL, dan pengaturan lain.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   **CRITICAL SETTING:** 
   Pastikan konfigurasi *Queue* dan *Cache* menggunakan `database`:
   ```env
   APP_ENV=production
   APP_DEBUG=false

   QUEUE_CONNECTION=database
   CACHE_STORE=database
   ```

5. **Migrasi Database & Build Assets**
   ```bash
   php artisan migrate --force
   ```

---

## 3. Konfigurasi Queue Worker (Supervisor)

Karena fitur **Export (Excel & PDF)** di STO dapat memakan waktu dan _memory_ yang besar, proses pembuatan dokumen dilakukan secara _asynchronous_ (berjalan di _background_). Jika Anda tidak menjalankan Queue Worker, *export* akan macet di status **"Memproses"** selamanya.

Kita akan menggunakan **Supervisor** untuk memastikan _script worker_ tetap berjalan dan me-_restart_ dirinya sendiri otomatis saat _crash_ atau saat server di-_reboot_.

### Langkah 3.1 - Install Supervisor
```bash
sudo apt-get update
sudo apt-get install supervisor
```

### Langkah 3.2 - Pasang File Konfigurasi
Aplikasi STO sudah dilengkapi *file* _template_ bawaan di folder `server/adasi-sto-worker.conf`. 
Salin _file_ tersebut ke folder konfigurasi sistem Supervisor:

```bash
sudo cp /var/www/adasi_sto/server/adasi-sto-worker.conf /etc/supervisor/conf.d/adasi-sto-worker.conf
```

Jika Anda ingin membuat dari awal, buat _file_ konfigurasi via editor teks (misal: `nano`):
```bash
sudo nano /etc/supervisor/conf.d/adasi-sto-worker.conf
```
Dan isi dengan:
```ini
[program:adasi-sto-worker]
process_name=%(program_name)s_%(process_num)02d

# PERHATIAN: Sesuaikan /var/www/adasi_sto dengan path sebenarnya!
command=php /var/www/adasi_sto/artisan queue:work database --sleep=3 --tries=3 --max-time=3600

autostart=true
autorestart=true
stopasgroup=true
killasgroup=true

# Sesuaikan dengan user web server Anda (contoh: ubuntu, root, www-data)
user=www-data
numprocs=1
redirect_stderr=true

# Lokasi log untuk membaca error worker jika export gagal
stdout_logfile=/var/www/adasi_sto/storage/logs/worker.log
stopwaitsecs=3600
```

### Langkah 3.3 - Jalankan Supervisor
Setelah menyimpan file konfigurasi, perintahkan Supervisor untuk mendeteksi *file* baru dan menyalakannya:
```bash
# Membaca ulang direktori conf.d/
sudo supervisorctl reread

# Memasukkan konfigurasi adasi-sto-worker ke sistem supervisor
sudo supervisorctl update

# Menjalankan worker
sudo supervisorctl start adasi-sto-worker:*
```

### Langkah 3.4 - Verifikasi
Untuk memastikan *worker* sudah berjalan normal, periksa statusnya menggunakan:
```bash
sudo supervisorctl status
```
_Output_ yang diharapkan:
```
adasi-sto-worker:adasi-sto-worker_00   RUNNING   pid 12345, uptime 0:00:15
```

### Langkah 3.5 - Alternatif: Konfigurasi Queue via Cron Job (Khusus Shared Hosting / cPanel)

Jika Anda _deploy_ di lingkungan **cPanel / Shared Hosting** yang tidak mengizinkan instalasi Supervisor, Anda wajib mengandalkan **Cron Jobs**.

Aplikasi ini sudah diprogram (di dalam `routes/console.php`) agar otomatis menjalankan _worker_ setiap menit jika _scheduler_ utama Laravel berjalan.

1. Buka menu **Cron Jobs** di cPanel Anda.
2. Tambahkan Cron Job baru dengan pengaturan **Once Per Minute (* * * * *)**.
3. Isi kolom _Command_ dengan perintah berikut (sesuaikan `/home/usercpanel/public_html` dengan _path_ aplikasi Anda):

```bash
cd /home/usercpanel/public_html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

*(Catatan: pastikan letak *binary* PHP Anda benar. Di beberapa hosting menggunakan `/opt/cpanel/ea-php82/root/usr/bin/php` atau sekadar `php`)*.

Setiap menit, Cron Job akan memicu Laravel Scheduler, dan Scheduler akan menjalankan `php artisan queue:work --stop-when-empty`. Jika tidak ada export yang sedang diantre, *worker* akan langsung mati, sehingga tidak membebani server (hemat CPU cPanel).

---

## 4. Maintenance / Deploy Pembaruan (CI/CD)

Saat ada pembaruan kode aplikasi di masa mendatang (misalnya *git pull* terbaru), Anda diwajibkan untuk memuat ulang _worker_ agar kode baru segera ter-_load_.

Berikut perintah standar _update/deploy_:
```bash
cd /var/www/adasi_sto
git pull origin main
composer install --no-dev --optimize-autoloader

# Jika ada perubahan skema DB
php artisan migrate --force

# Reset Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# CRITICAL: Restart Worker agar Export menggunakan kode terbaru
php artisan queue:restart
```

Perintah `php artisan queue:restart` akan memerintahkan *worker* yang sedang berjalan agar bunuh diri (mati) setelah tugasnya saat ini selesai. Supervisor akan secara otomatis membangkitkannya kembali dengan kode yang terbaru (tanpa memotong export yang sedang setengah jalan).

---

## 5. Troubleshooting (Pemecahan Masalah)

1. **Pesan Error pada Pop-up: "Export gagal dimulai."**
   * Solusi: Cek _permissions_ atau periksa file `/var/www/adasi_sto/storage/logs/laravel.log`. Biasanya ini masalah ekstensi PHP (misalnya DOMPDF butuh ekstensi GD) atau koneksi *database* gagal.

2. **Status Export tersangkut di tulisan "Memproses" terus-menerus dan tidak mau terdownload**
   * Solusi: Worker *queue* Anda tidak berjalan.
   * Cek via `sudo supervisorctl status` pastikan statusnya `RUNNING`.
   * Jika tidak ada supervisor, jalankan `php artisan queue:work` secara manual di terminal untuk mengetes apakah ada pesan *error*.

3. **Status berubah menjadi "Gagal" (Failed) saat Export PDF Data Besar**
   * Solusi: Export PDF memakan memori tinggi. Cek log _error_ di `/var/www/adasi_sto/storage/logs/worker.log`. 
   * Jika log menunjukkan pesan _"Allowed memory size of X bytes exhausted"_, artinya RAM PHP mencapai batas.
   * Anda bisa meningkatkan batas `memory_limit` pada `php.ini` sistem, atau di Laravel.

---

### *Dokumen Selesai*
