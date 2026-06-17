# STO Scan To Office

STO adalah aplikasi Laravel 12 untuk proses Scan To Office material, dengan role `admin` dan `scanner`, active STO, parser QR final, audit log, export queued, dan dashboard admin server-side.

## Requirement

- PHP 8.2+
- Composer
- Node.js dan npm
- MySQL
- Queue worker untuk export

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

## Queue Export

Export scan result berjalan queued. Jalankan worker:

```bash
php artisan queue:work database --queue=default --sleep=3 --tries=3 --timeout=300
```

Setelah deploy:

```bash
php artisan queue:restart
```

## Deploy Production

Gunakan `.env.example` sebagai baseline production:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `SESSION_DRIVER=database`
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true`
- `QUEUE_CONNECTION=database`
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

Dokumentasi requirement dan operasional ada di:

- `docs/AGENTS.md`
- `docs/DESIGN.md`
- `docs/DATABASE.md`
- `docs/BARCODE_PARSING.md`
- `docs/API_SPECIFICATION.md`
- `docs/OPERATIONS_RUNBOOK.md`

