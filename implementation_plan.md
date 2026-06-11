# STO (Scan To Office) — Implementation Plan

Sistem web stock opname material berbasis QR Code / Barcode untuk pencatatan material secara real-time dengan dua aktor utama: **User (Scanner)** dan **Admin**.

**Stack**: Laravel 12 + PHP 8.2 + MySQL + Vite + TailwindCSS 3 + Alpine.js + Yajra DataTables

---

## User Review Required

> [!IMPORTANT]
> **Konsistensi dengan project yang sudah ada**: Plan ini mengikuti konvensi yang sudah dipakai di `adasi_portal_supplier` — Laravel 12, Breeze auth, TailwindCSS, Alpine.js, Yajra DataTables, maatwebsite/excel, dan Hashids. Apakah Anda ingin setup identik?

> [!WARNING]
> **Database terpisah**: Sistem STO ini akan menggunakan database MySQL baru bernama `adasi_sto`. Pastikan MySQL sudah running di Laragon.

> [!IMPORTANT]
> **Barcode Scanner Library**: Plan ini menggunakan library **html5-qrcode** (JavaScript) untuk camera-based barcode scanning di mobile device. Library ini gratis dan support Code128, QR Code, serta format barcode industri lainnya. Apakah ada preferensi library barcode scanner lain?

---

## Open Questions

> [!IMPORTANT]
> 1. **Login credential**: Apakah user dan admin dibuat manual via seeder, atau perlu halaman registrasi?
> 2. **PIC (Person In Charge)**: Apakah PIC merupakan list user yang sudah terdaftar, atau input teks bebas?
> 3. **Lot**: Apakah field Lot diinput manual oleh user setelah scan, atau ada di dalam barcode?
> 4. **Qty**: Apakah Qty default = 1 per scan, atau user bisa input qty manual?
> 5. **STO Code format**: Contoh di dokumen `STO2606-001`. Apakah kode ini di-generate otomatis oleh sistem, atau diinput manual oleh user?
> 6. **Suffix (karakter terakhir barcode, misal "B")**: Apakah suffix ini perlu disimpan atau didisplay? Apa artinya?

---

## Proposed Changes

### Phase 1 — Project Scaffolding

Setup Laravel 12 project baru di `c:\laragon\www\adasi_sto` dengan semua dependency.

#### [NEW] Project Initialization (via CLI)
```bash
# Create Laravel project
composer create-project laravel/laravel . --prefer-dist

# Install auth scaffolding
composer require laravel/breeze --dev
php artisan breeze:install blade

# Install packages (matching adasi_portal_supplier conventions)
composer require yajra/laravel-datatables-oracle:~12.0
composer require maatwebsite/excel
composer require vinkla/hashids

# Install frontend
npm install
npm install html5-qrcode
```

#### [NEW] [.env](file:///c:/laragon/www/adasi_sto/.env)
- `DB_DATABASE=adasi_sto`
- `DB_USERNAME=root`
- `DB_PASSWORD=` (Laragon default)
- `APP_NAME="STO - Scan To Office"`

---

### Phase 2 — Database Schema & Migrations

7 tabel utama + 1 tabel audit log. Semua migration mengikuti konvensi Laravel.

#### [NEW] `database/migrations/0001_01_01_000000_create_users_table.php`
Default Laravel users table + tambahan fields:
```
users
├── id (bigint, PK, auto-increment)
├── name (varchar 255)
├── email (varchar 255, unique)
├── password (varchar 255)
├── role (enum: 'admin', 'user')
├── is_active (boolean, default true)
├── email_verified_at (timestamp, nullable)
├── remember_token (varchar 100, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

#### [NEW] `database/migrations/2026_06_10_000001_create_plants_table.php`
```
plants
├── id (bigint, PK)
├── name (varchar 100) — "Cikarang", "Deltamas", "Surabaya"
├── code (varchar 20, unique)
├── is_active (boolean, default true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

#### [NEW] `database/migrations/2026_06_10_000002_create_master_materials_table.php`
```
master_materials
├── id (bigint, PK)
├── code (varchar 10, unique) — "1H", "2P", "2L", etc.
├── name (varchar 100) — "SKD11", "SKD61", etc.
├── is_active (boolean, default true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

#### [NEW] `database/migrations/2026_06_10_000003_create_master_keterangan_table.php`
```
master_keterangan
├── id (bigint, PK)
├── name (varchar 100) — "OK", "Lot Salah", "Size Salah", "Material Salah"
├── is_active (boolean, default true)
├── created_at (timestamp)
└── updated_at (timestamp)
```

#### [NEW] `database/migrations/2026_06_10_000004_create_locations_table.php`
```
locations
├── id (bigint, PK)
├── plant_id (FK → plants.id)
├── name (varchar 100) — rack/lokasi name
├── created_at (timestamp)
└── updated_at (timestamp)
```
- Index: `plant_id`

#### [NEW] `database/migrations/2026_06_10_000005_create_sto_sessions_table.php`
```
sto_sessions
├── id (bigint, PK)
├── sto_code (varchar 50, unique) — "STO2606-001"
├── user_id (FK → users.id)
├── plant_id (FK → plants.id)
├── pic (varchar 255) — Person In Charge
├── status (enum: 'active', 'completed') — default 'active'
├── created_at (timestamp)
└── updated_at (timestamp)
```
- Indexes: `user_id`, `plant_id`, `sto_code`

#### [NEW] `database/migrations/2026_06_10_000006_create_scan_results_table.php`
```
scan_results
├── id (bigint, PK)
├── user_id (FK → users.id)
├── sto_session_id (FK → sto_sessions.id)
├── plant_id (FK → plants.id)
├── location_id (FK → locations.id)
├── barcode_material (varchar 100) — raw barcode string
├── material_code (varchar 10) — parsed: "1H"
├── material_name (varchar 100) — looked up: "SKD11"
├── shape_code (varchar 10) — parsed: "RF"
├── shape_name (varchar 50) — mapped: "Flat"
├── thickness (decimal 10,2, nullable) — only for Flat
├── width (decimal 10,2, nullable) — only for Flat
├── diameter (decimal 10,2, nullable) — only for Round
├── length (decimal 10,2, nullable)
├── qty (integer, default 1)
├── lot (varchar 100, nullable)
├── scan_time (timestamp) — actual scan timestamp
├── keterangan (varchar 100, default 'OK')
├── created_at (timestamp)
└── updated_at (timestamp)
```
- Indexes: `user_id`, `sto_session_id`, `plant_id`, `location_id`, `barcode_material`, `material_code`
- Composite index: `(user_id, sto_session_id)` for user data isolation

#### [NEW] `database/migrations/2026_06_10_000007_create_scan_result_logs_table.php`
```
scan_result_logs
├── id (bigint, PK)
├── scan_result_id (FK → scan_results.id)
├── user_id (FK → users.id) — who made the change
├── action (enum: 'created', 'updated', 'deleted')
├── old_values (json, nullable)
├── new_values (json, nullable)
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

### Phase 3 — Models & Relationships

#### [NEW] `app/Models/User.php`
- Relationships: `hasMany(ScanResult)`, `hasMany(StoSession)`
- Helper methods: `isAdmin()`, `isUser()`
- Uses: `HasFactory`, `Notifiable`, `HasHashids`

#### [NEW] `app/Models/Plant.php`
- Relationships: `hasMany(Location)`, `hasMany(StoSession)`, `hasMany(ScanResult)`
- Fillable: `name`, `code`, `is_active`

#### [NEW] `app/Models/MasterMaterial.php`
- Static method: `findByCode($code)` → lookup material name by code
- Fillable: `code`, `name`, `is_active`

#### [NEW] `app/Models/MasterKeterangan.php`
- Fillable: `name`, `is_active`

#### [NEW] `app/Models/Location.php`
- Relationships: `belongsTo(Plant)`, `hasMany(ScanResult)`
- Fillable: `plant_id`, `name`

#### [NEW] `app/Models/StoSession.php`
- Relationships: `belongsTo(User)`, `belongsTo(Plant)`, `hasMany(ScanResult)`
- Fillable: `sto_code`, `user_id`, `plant_id`, `pic`, `status`

#### [NEW] `app/Models/ScanResult.php`
- Relationships: semua `belongsTo` (User, StoSession, Plant, Location)
- Default ordering: `latest()` (ORDER BY created_at DESC)
- Scope: `scopeForUser($query, $userId)` — isolasi data user
- Fillable: semua field scan
- Uses: `HasHashids`

#### [NEW] `app/Models/ScanResultLog.php`
- Relationships: `belongsTo(ScanResult)`, `belongsTo(User)`
- Fillable: `scan_result_id`, `user_id`, `action`, `old_values`, `new_values`

---

### Phase 4 — Barcode Parsing Engine

Core logic untuk memparse barcode berdasarkan format yang didefinisikan.

#### [NEW] `app/Services/BarcodeParser.php`

```php
class BarcodeParser
{
    /**
     * Parse barcode string dan return structured data.
     *
     * Format Flat: RF{material_code}{thickness:3}{width:4}{length:4}{suffix:1}
     *   Contoh: RF1H059-00960099B
     *   → shape=Flat, material=1H, thickness=59, width=96, length=99
     *
     * Format Round: RR{material_code}{diameter:3}-{padding:4}{length:4}{suffix:1}
     *   Contoh: RR2P051-00000835B
     *   → shape=Round, material=2P, diameter=51, length=835
     */
    public function parse(string $barcode): BarcodeResult
    {
        // 1. Remove dashes for positional parsing
        // 2. Detect shape by first 2 chars (RF/RR)
        // 3. Extract material code (chars 2-3, i.e., 2 chars)
        // 4. Parse dimensions based on shape
        // 5. Lookup material name from MasterMaterial
        // 6. Return BarcodeResult DTO
    }
}
```

#### [NEW] `app/DTOs/BarcodeResult.php`
```php
class BarcodeResult
{
    public string $barcode;
    public string $materialCode;
    public string $materialName;
    public string $shapeCode;
    public string $shapeName;
    public ?float $thickness;
    public ?float $width;
    public ?float $diameter;
    public ?float $length;
    public bool $isValid;
    public ?string $errorMessage;
}
```

**Parsing Rules**:
| Shape | Prefix | Dimensions | Unused Fields |
|-------|--------|------------|---------------|
| Flat  | `RF`   | Thickness, Width, Length | Diameter |
| Round | `RR`   | Diameter, Length | Thickness, Width |

**Barcode String Dissection** (setelah hapus dash `-`):

**RF (Flat)**: `RF1H05900960099B` → total 17 chars
| Pos | Length | Field |
|-----|--------|-------|
| 0-1 | 2 | Shape Code (`RF`) |
| 2-3 | 2 | Material Code (`1H`) |
| 4-6 | 3 | Thickness (`059` → 59) |
| 7-10 | 4 | Width (`0096` → 96) |
| 11-14 | 4 | Length (`0099` → 99) |
| 15 | 1 | Suffix (`B`) |

**RR (Round)**: `RR2P05100000835B` → total 17 chars
| Pos | Length | Field |
|-----|--------|-------|
| 0-1 | 2 | Shape Code (`RR`) |
| 2-3 | 2 | Material Code (`2P`) |
| 4-6 | 3 | Diameter (`051` → 51) |
| 7-10 | 4 | Padding/zeros |
| 11-14 | 4 | Length (`0835` → 835) |
| 15 | 1 | Suffix (`B`) |

---

### Phase 5 — Controllers & Routes

#### [NEW] `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- Login / Logout (dari Breeze, customized)
- Redirect berdasarkan role: admin → `/admin/dashboard`, user → `/scan`

#### [NEW] `app/Http/Controllers/ScanController.php`
Untuk User (Operator Scanner):
- `index()` — Dashboard user + list scan results (DataTable server-side)
- `setup()` — Halaman setup sebelum scan (pilih PIC, Plant, STO Code, Location)
- `storeSetup()` — Simpan session STO
- `scan()` — Halaman scanner camera
- `storeScan(Request $request)` — Process barcode, parse, validate, store
- `updateKeterangan($id)` — Update keterangan scan result
- `getLocations($plantId)` — AJAX: get locations by plant
- `storeLocation(Request $request)` — AJAX: add new location
- `datatable()` — Server-side DataTable endpoint

#### [NEW] `app/Http/Controllers/Admin/DashboardController.php`
- `index()` — Admin dashboard dengan statistik
- `scanResults()` — Halaman monitoring scan results (DataTable server-side)
- `datatable()` — Server-side DataTable endpoint (semua data)
- `edit($id)` — Edit scan result
- `update($id)` — Update scan result
- `destroy($id)` — Delete scan result (soft + audit log)
- `export()` — Export ke Excel
- `barcodeOverview()` — Overview barcode sama (grouping)
- `overviewDatatable()` — Server-side DataTable untuk overview

#### [NEW] `app/Http/Controllers/Admin/MasterController.php`
- CRUD untuk master data: Plant, Material, Keterangan, Location
- `plants()`, `storePlant()`, `updatePlant()`, `destroyPlant()`
- `materials()`, `storeMaterial()`, `updateMaterial()`, `destroyMaterial()`
- `keterangan()`, `storeKeterangan()`, `updateKeterangan()`, `destroyKeterangan()`

#### [NEW] `app/Http/Middleware/AdminMiddleware.php`
- Check `auth()->user()->role === 'admin'`
- Redirect ke `/scan` jika bukan admin

#### [NEW] `routes/web.php`
```php
// Auth routes (Breeze)
require __DIR__.'/auth.php';

// User (Scanner) routes
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => redirect()->route('scan.setup'));
    Route::get('/scan/setup', [ScanController::class, 'setup'])->name('scan.setup');
    Route::post('/scan/setup', [ScanController::class, 'storeSetup'])->name('scan.store-setup');
    Route::get('/scan', [ScanController::class, 'scan'])->name('scan.index');
    Route::post('/scan', [ScanController::class, 'storeScan'])->name('scan.store');
    Route::get('/scan/results', [ScanController::class, 'index'])->name('scan.results');
    Route::put('/scan/{id}/keterangan', [ScanController::class, 'updateKeterangan'])->name('scan.update-keterangan');
    Route::get('/api/locations/{plantId}', [ScanController::class, 'getLocations']);
    Route::post('/api/locations', [ScanController::class, 'storeLocation']);
    Route::get('/scan/datatable', [ScanController::class, 'datatable'])->name('scan.datatable');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/scan-results', [DashboardController::class, 'scanResults'])->name('scan-results');
    Route::get('/scan-results/datatable', [DashboardController::class, 'datatable'])->name('scan-results.datatable');
    Route::get('/scan-results/{id}/edit', [DashboardController::class, 'edit'])->name('scan-results.edit');
    Route::put('/scan-results/{id}', [DashboardController::class, 'update'])->name('scan-results.update');
    Route::delete('/scan-results/{id}', [DashboardController::class, 'destroy'])->name('scan-results.destroy');
    Route::get('/scan-results/export', [DashboardController::class, 'export'])->name('scan-results.export');
    Route::get('/barcode-overview', [DashboardController::class, 'barcodeOverview'])->name('barcode-overview');
    Route::get('/barcode-overview/datatable', [DashboardController::class, 'overviewDatatable'])->name('barcode-overview.datatable');
    
    // Master data
    Route::resource('plants', MasterController::class . '@plants');
    Route::resource('materials', MasterController::class . '@materials');
    Route::resource('keterangan', MasterController::class . '@keterangan');
});
```

---

### Phase 6 — Views & UI/UX Design

Premium dark-mode UI dengan glassmorphism, gradient accents, dan micro-animations.

**Design System**:
- **Color Palette**: Dark navy base (`#0f172a` → `#1e293b`), accent gradient (`#6366f1` → `#8b5cf6` indigo-to-violet), success green (`#10b981`), warning amber (`#f59e0b`), danger rose (`#f43f5e`)
- **Typography**: Google Fonts "Inter" (body) + "JetBrains Mono" (barcode/data)
- **Cards**: Glassmorphism — `backdrop-blur-xl bg-white/5 border border-white/10`
- **Animations**: Fade-in on page load, slide-up for scan results, pulse for scan button, count-up for statistics

#### [NEW] `resources/views/layouts/app.blade.php`
- Master layout dengan sidebar navigation
- Dark theme base
- Alpine.js integration
- Responsive: sidebar collapse di mobile
- Flash message / toast notification component
- CSS imports (Tailwind + custom)

#### [NEW] `resources/views/layouts/guest.blade.php`
- Layout untuk halaman login
- Centered card with background gradient

#### [NEW] `resources/views/auth/login.blade.php`
- Premium login form
- Glassmorphism card
- Animated background gradient
- STO branding

#### [NEW] `resources/views/scan/setup.blade.php`
**Halaman Setup STO** (Mobile-optimized):
- Card: Select PIC
- Card: Select Plant (dropdown)
- Card: Input STO Code
- Card: Select/Add Location (dynamic, AJAX load by plant)
- Button: "Mulai Scan" → redirect ke scanner
- Auto-save session ke `sto_sessions`

#### [NEW] `resources/views/scan/scanner.blade.php`
**Halaman Scanner** (Mobile-first, fullscreen capable):
- Camera viewfinder (html5-qrcode)
- Status bar: Plant, STO Code, Location aktif
- Scan result instant feedback (slide-up toast)
- Recent scan list (last 5)
- Quick toggle: Lot input, Qty input
- Button: Manual barcode input (fallback keyboard)
- Sound effect on successful scan ✓
- Vibration on scan (mobile) ✓

#### [NEW] `resources/views/scan/results.blade.php`
**Dashboard User — Hasil Scan**:
- Stat Cards (animated count-up):
  - Total Scan Hari Ini
  - Total Scan STO Aktif
  - Plant Aktif
  - Lokasi Aktif
- DataTable (server-side):
  - No (descending), Barcode, Material, Shape, Size, Lot, Jam, Keterangan
  - Inline edit keterangan (dropdown)
  - Row numbering descending (Rule 2)
  - Data terbaru di atas (Rule 1)

#### [NEW] `resources/views/admin/dashboard.blade.php`
**Dashboard Admin**:
- Stat Cards (glassmorphism, animated):
  - Total Scan Keseluruhan (big number + sparkline chart)
  - Total Scan Hari Ini
  - Total User Aktif
  - Total Plant
- Chart: Scan Per User (horizontal bar, Chart.js)
- Chart: Scan Per Plant (doughnut, Chart.js)
- Chart: Scan Trend 7 Hari (line chart)
- Quick links ke fitur admin

#### [NEW] `resources/views/admin/scan-results.blade.php`
**Monitoring Scan Results (Admin)**:
- Filter bar: Plant, User, Date Range, STO Code, Keterangan
- DataTable (server-side) with all columns:
  - No, Barcode, Material, Shape, T, W, D, L, Qty, Lot, User, Plant, Lokasi, Jam, Keterangan
- Actions per row: Edit (modal), Delete (confirm)
- Bulk actions: Export selected
- Export All button → Excel

#### [NEW] `resources/views/admin/scan-results-edit.blade.php`
- Modal form untuk edit scan result
- Fields: Lot, Qty, Keterangan, Location
- Audit log preview

#### [NEW] `resources/views/admin/barcode-overview.blade.php`
**Overview Barcode Sama**:
- DataTable grouped by barcode_material
- Columns: Barcode, Material, Shape, Size, Qty Total, Jumlah Scan
- Filter: Plant, STO Code, Date Range

#### [NEW] `resources/views/admin/master/plants.blade.php`
#### [NEW] `resources/views/admin/master/materials.blade.php`
#### [NEW] `resources/views/admin/master/keterangan.blade.php`
- CRUD tables untuk master data
- Inline add/edit dengan modal
- Activate/deactivate toggle

#### [NEW] `resources/views/components/stat-card.blade.php`
- Reusable stat card component
- Props: title, value, icon, color, trend

#### [NEW] `resources/views/components/scan-row.blade.php`
- Reusable scan result row component

---

### Phase 7 — Export & DataTables

#### [NEW] `app/Exports/ScanResultsExport.php`
```php
class ScanResultsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    // Export ke Excel dengan filter (plant, user, date, sto_code)
    // Headings: No, Barcode, Material, Shape, T, W, D, L, Qty, Lot, User, Plant, Lokasi, Waktu Scan, Keterangan
    // Styling: header bold, auto-width, border
}
```

#### [NEW] `app/DataTables/ScanResultDataTable.php`
- Server-side processing untuk scan results
- Support filter: plant, user, date range, sto_code, keterangan
- Ordering: `created_at DESC` (default)
- Row number: descending

#### [NEW] `app/DataTables/BarcodeOverviewDataTable.php`
- Server-side grouping by barcode_material
- Aggregate: `SUM(qty)` as qty_total, `COUNT(*)` as scan_count

---

### Phase 8 — Seeders & Final Setup

#### [NEW] `database/seeders/DatabaseSeeder.php`
Calls all seeders in order.

#### [NEW] `database/seeders/UserSeeder.php`
```php
// Admin account
User::create([
    'name' => 'Admin STO',
    'email' => 'admin@adasi.co.id',
    'password' => Hash::make('password'),
    'role' => 'admin',
]);

// Test user accounts
User::create([
    'name' => 'Operator 1',
    'email' => 'operator1@adasi.co.id',
    'password' => Hash::make('password'),
    'role' => 'user',
]);

User::create([
    'name' => 'Operator 2',
    'email' => 'operator2@adasi.co.id',
    'password' => Hash::make('password'),
    'role' => 'user',
]);
```

#### [NEW] `database/seeders/PlantSeeder.php`
```php
Plant::insert([
    ['code' => 'CKR', 'name' => 'Cikarang'],
    ['code' => 'DLT', 'name' => 'Deltamas'],
    ['code' => 'SBY', 'name' => 'Surabaya'],
]);
```

#### [NEW] `database/seeders/MasterMaterialSeeder.php`
```php
MasterMaterial::insert([
    ['code' => '1H', 'name' => 'SKD11'],
    ['code' => '2P', 'name' => 'SKD61'],
    ['code' => '2L', 'name' => 'DHAW'],
    ['code' => '4F', 'name' => 'P20'],
    ['code' => '4E', 'name' => 'NAK80'],
    ['code' => '1B', 'name' => 'DC53'],
]);
```

#### [NEW] `database/seeders/MasterKeteranganSeeder.php`
```php
MasterKeterangan::insert([
    ['name' => 'OK'],
    ['name' => 'Lot Salah'],
    ['name' => 'Size Salah'],
    ['name' => 'Material Salah'],
]);
```

---

## File Structure Summary

```
adasi_sto/
├── app/
│   ├── DTOs/
│   │   └── BarcodeResult.php
│   ├── DataTables/
│   │   ├── ScanResultDataTable.php
│   │   └── BarcodeOverviewDataTable.php
│   ├── Exports/
│   │   └── ScanResultsExport.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthenticatedSessionController.php
│   │   │   ├── Admin/
│   │   │   │   ├── DashboardController.php
│   │   │   │   └── MasterController.php
│   │   │   └── ScanController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Plant.php
│   │   ├── MasterMaterial.php
│   │   ├── MasterKeterangan.php
│   │   ├── Location.php
│   │   ├── StoSession.php
│   │   ├── ScanResult.php
│   │   └── ScanResultLog.php
│   ├── Services/
│   │   └── BarcodeParser.php
│   └── Traits/
│       └── HasHashids.php
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2026_06_10_000001_create_plants_table.php
│   │   ├── 2026_06_10_000002_create_master_materials_table.php
│   │   ├── 2026_06_10_000003_create_master_keterangan_table.php
│   │   ├── 2026_06_10_000004_create_locations_table.php
│   │   ├── 2026_06_10_000005_create_sto_sessions_table.php
│   │   ├── 2026_06_10_000006_create_scan_results_table.php
│   │   └── 2026_06_10_000007_create_scan_result_logs_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── PlantSeeder.php
│       ├── MasterMaterialSeeder.php
│       └── MasterKeteranganSeeder.php
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── barcode-scanner.js
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── guest.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       ├── scan/
│       │   ├── setup.blade.php
│       │   ├── scanner.blade.php
│       │   └── results.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── scan-results.blade.php
│       │   ├── scan-results-edit.blade.php
│       │   ├── barcode-overview.blade.php
│       │   └── master/
│       │       ├── plants.blade.php
│       │       ├── materials.blade.php
│       │       └── keterangan.blade.php
│       └── components/
│           ├── stat-card.blade.php
│           └── scan-row.blade.php
├── routes/
│   ├── web.php
│   └── auth.php
└── public/
    └── sounds/
        └── scan-success.mp3
```

**Total new files: ~45 files**

---

## Verification Plan

### Automated Tests

```bash
# Run migrations
php artisan migrate:fresh --seed

# Verify barcode parsing
php artisan tinker
# >>> (new App\Services\BarcodeParser)->parse('RF1H059-00960099B')
# >>> (new App\Services\BarcodeParser)->parse('RR2P051-00000835B')

# Run dev server
composer dev
# atau: php artisan serve + npm run dev
```

### Manual Verification

1. **Login Flow**: Login sebagai admin dan user, verify redirect yang benar
2. **Setup STO**: User memilih PIC, Plant, STO Code, Location → session tersimpan
3. **Scan Flow**: Buka scanner di mobile, scan barcode test → data tersimpan
4. **Barcode Parsing**: Test kedua format (RF dan RR) → parsing benar
5. **Data Isolation**: Login user A, verify hanya lihat data sendiri (Rule 3)
6. **Sort Order**: Verify data terbaru di atas + nomor descending (Rule 1 & 2)
7. **Validation**: Scan barcode invalid → tampil pesan error (Rule 4)
8. **Admin Dashboard**: Verify stats, charts, dan DataTable semua data
9. **Export**: Export Excel → file terdownload dengan data yang benar
10. **Barcode Overview**: Verify grouping dan qty total akurat
11. **Responsive**: Test UI di mobile browser — semua elemen accessible
12. **CRUD Master Data**: Admin bisa add/edit/delete plant, material, keterangan
