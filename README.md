# STO Scan To Office

STO adalah aplikasi Laravel 12 untuk proses Scan To Office material, dengan role `admin` dan `scanner`, capability scanner validator, Active STO, workflow barcode/print, Rack Confirmation per STO, audit hard delete, export synchronous, dan tabel server-side.

## Requirement

- PHP 8.2+
- Composer
- Node.js dan npm
- MySQL

## Setup Lokal

```bash
composer install
npm install
npm run vendor:publish
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Jika memakai Laragon atau virtual host lokal, arahkan document root ke folder `public`.

## Asset Frontend

Runtime production tidak bergantung CDN. Asset vendor disalin dari `node_modules` ke `public/vendor`.

```bash
npm run vendor:publish
```

`npm run build` otomatis menjalankan `vendor:publish` sebelum Vite build.

## Export

Export scan result saat ini berjalan secara synchronous di dalam request HTTP. Project belum mengoperasikan queue worker. Saat export berjalan, browser harus menunggu response selesai; hindari mengirim request export yang sama berulang kali.

Class job dan `ExportRequest` yang tersedia adalah infrastructure awal dan bukan bukti bahwa queued export sudah aktif. Penerapan worker, retry, monitoring, serta perubahan UI status export harus dilakukan sebagai pekerjaan deployment terpisah.

## Deploy Production

Gunakan `.env.example` sebagai baseline production:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `SESSION_DRIVER=database`
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true`
- `QUEUE_CONNECTION=sync` selama queue worker belum diterapkan
- `CACHE_STORE=database`
- `TRUSTED_PROXIES` sesuai reverse proxy/load balancer

Perintah deploy umum:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Jika seeder dijalankan di production, segera ganti password default akun `admin`, `operator1`, dan `operator2`.

## Verifikasi

```bash
php artisan route:list --except-vendor
php artisan view:cache
php artisan test
php artisan optimize:clear
composer validate --strict
```

## Dokumentasi

Dokumen canonical yang tersedia di repository:

- `AGENTS.md` - engineering conventions, business constraints, dan source of truth kode.
- `DESIGN.md` - pedoman canonical UI/UX, responsive behavior, accessibility, dan page patterns.
- `DATABASE.md` - kontrak tabel, relasi, index, lifecycle, dan transaction.
- `API_SPECIFICATION.md` - endpoint aktif, payload, response, dan runtime export.
- `AUTHORIZATION_MATRIX.md` - hak akses admin, scanner, dan scanner validator.
