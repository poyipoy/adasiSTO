<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\MaterialDoubleController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ScanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| System & Authentication Routes
|--------------------------------------------------------------------------
| Menangani check status server, redirector halaman utama, 
| serta proses login/logout.
*/
// Rute untuk mengecek status nyala-tidaknya server (health check)
Route::get('/health', HealthController::class)->name('health');

// Redirect awal aplikasi. Jika belum login akan dialihkan ke halaman login.
// Jika sudah login, akan diarahkan sesuai perannya (Admin ke dashboard, Scanner ke setup).
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->to(route('login'));
    }

    return Auth::user()->isAdmin()
        ? redirect()->to(route('admin.dashboard'))
        : redirect()->to(route('scan.setup'));
});

Route::middleware('guest')->group(function () {
    // Halaman antarmuka/form untuk login
    Route::get('/login', fn () => view('auth.login'))->name('login');

    // Memproses data form login (validasi kredensial username & password)
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'username' => 'Username atau password salah.',
            ])->onlyInput('username');
        }

        if (!Auth::user()->is_active) {
            Auth::logout();

            return back()->withErrors([
                'username' => 'User tidak aktif.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();

        return Auth::user()->isAdmin()
            ? redirect()->intended(route('admin.dashboard'))
            : redirect()->intended(route('scan.setup'));
    })->middleware('throttle:login')->name('login.store');
});

// Memproses permintaan logout, menghapus session user, dan mereset token keamanan
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->to(route('login'));
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Scanner User Routes
|--------------------------------------------------------------------------
| Rute-rute ini hanya bisa diakses oleh user dengan role 'scanner'.
| Menangani halaman scanner utama, histori scan, serta endpoint API 
| untuk memproses data dari alat scanner barcode.
*/
Route::middleware(['auth', 'role:scanner'])->group(function () {
    // Halaman ringkasan statistik pribadi (overview) khusus untuk user scanner
    Route::get('/scan/overview', [ScanController::class, 'overview'])->name('scan.overview');
    // API pengambilan data ringkasan scanner (dipakai untuk chart/tabel)
    Route::get('/api/scan/overview', [ScanController::class, 'overviewData'])->middleware('throttle:datatable')->name('api.scan.overview');
    
    // Halaman penentuan sesi operasional (memilih STO aktif, Plant, dan Lokasi Rak)
    Route::get('/scan/setup', [ScanController::class, 'setup'])->name('scan.setup');
    // Memproses pemilihan sesi operasional dan menyimpannya di session
    Route::post('/scan/setup', [ScanController::class, 'storeSetup'])->name('scan.setup.store');
    
    // Halaman operasional utama tempat menggunakan alat scanner tembak (gun scanner)
    Route::get('/scan/scanner', [ScanController::class, 'scanner'])->name('scan.scanner');
    // Halaman riwayat (history) daftar seluruh hasil scan dari user tersebut
    Route::get('/scan/history', [ScanController::class, 'historyPage'])->name('scan.history');
    // Halaman agregat rangkuman material dari apa yang telah discan oleh user
    Route::get('/scan/material-summary', [ScanController::class, 'materialSummary'])->name('scan.material-summary');

    // --- API ENDPOINTS UNTUK APLIKASI SCANNER ---
    
    // Mengambil daftar Lokasi Rak dinamis berdasarkan ID Plant yang dipilih
    Route::get('/api/locations', [ScanController::class, 'locations'])->middleware('throttle:datatable')->name('api.locations');
    // Membuat Lokasi Rak baru secara dinamis (on-the-fly) dari form setup
    Route::post('/api/locations', [ScanController::class, 'storeLocation'])->middleware('throttle:scan-write')->name('api.locations.store');
    // Menghapus Lokasi Rak yang belum pernah dipakai untuk scan
    Route::delete('/api/locations/{id}', [ScanController::class, 'destroyLocation'])->middleware('throttle:scan-write')->name('api.locations.destroy');
    
    // Membaca/Parsing teks raw dari QR code dan menerjemahkannya ke material attributes
    Route::post('/api/scan/preview', [ScanController::class, 'preview'])->middleware('throttle:scan-write')->name('api.scan.preview');
    // Memeriksa apakah suatu barcode pernah di-scan sebelumnya (Duplicate Warning)
    Route::post('/api/scan/check-duplicate', [ScanController::class, 'checkDuplicate'])->middleware('throttle:scan-write')->name('api.scan.check-duplicate');
    // Menyimpan final dari hasil scan ke dalam tabel scan_results di database
    Route::post('/api/scan/store', [ScanController::class, 'store'])->middleware('throttle:scan-write')->name('api.scan.store');
    
    // Endpoint API untuk memuat data tabel secara dinamis (DataTables)
    Route::get('/api/scan/recent', [ScanController::class, 'recent'])->middleware('throttle:datatable')->name('api.scan.recent');
    Route::get('/api/scan/history', [ScanController::class, 'history'])->middleware('throttle:datatable')->name('api.scan.history');
    Route::get('/api/scan/material-summary', [ScanController::class, 'materialSummaryData'])->middleware('throttle:datatable')->name('api.scan.material-summary');
    
    // Membatalkan atau menghapus baris hasil scan jika terjadi kesalahan (oleh scanner itu sendiri)
    Route::delete('/api/scan/{id}', [ScanController::class, 'destroy'])->middleware('throttle:scan-write')->name('api.scan.destroy');
});

/*
|--------------------------------------------------------------------------
| Material Double Validation Routes
|--------------------------------------------------------------------------
| Rute khusus admin/validator untuk menangani data scan yang ganda (duplicate).
| Menangani list duplikat, detail, validasi, dan penghapusan data.
*/
Route::middleware(['auth', 'material-double-access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Halaman antarmuka manajemen material ganda/duplikat
        Route::get('/material-double', [MaterialDoubleController::class, 'index'])->name('material-double');
        
        // Memuat rekapitulasi data barcode mana saja yang discan > 1 kali
        Route::get('/api/material-double', [MaterialDoubleController::class, 'datatable'])->middleware('throttle:datatable')->name('api.material-double');
        // Melihat deretan data mentah dari satu barcode yang sama (di-scan siapa saja, kapan, dsb)
        Route::get('/api/material-double/detail', [MaterialDoubleController::class, 'showDuplicateDetail'])->middleware('throttle:datatable')->name('api.material-double.detail');
        
        // Aksi menyetujui salah satu (atau beberapa) baris duplikat sebagai data valid
        Route::post('/api/material-double/validate', [MaterialDoubleController::class, 'validateDuplicate'])->name('api.material-double.validate');
        // Melakukan scan "tembak tiban" khusus jika baris duplikat yg ada salah semua
        Route::post('/api/material-double/scan', [MaterialDoubleController::class, 'scan'])
            ->middleware('throttle:scan-write')
            ->name('api.material-double.scan');
        // Menghapus baris-baris data duplikat yang disalahkan
        Route::delete('/api/material-double/delete-selected', [MaterialDoubleController::class, 'deleteSelected'])->name('api.material-double.delete-selected');
        
        // Export file excel laporan daftar material ganda melalui metode background jobs (antrean)
        Route::post('/api/material-double/export', [MaterialDoubleController::class, 'queueExport'])
            ->middleware('throttle:export')
            ->name('api.material-double.export.queue');
        // Polling melihat progres persen export background job
        Route::get('/api/material-double/export/status', [MaterialDoubleController::class, 'exportStatus'])->name('api.material-double.export.status');
        // Mengunduh file hasil export jika antrean job telah selesai
        Route::get('/api/material-double/export/{exportRequest}/download', [MaterialDoubleController::class, 'downloadExport'])->name('api.material-double.export.download');
    });

/*
|--------------------------------------------------------------------------
| Admin Portal Routes
|--------------------------------------------------------------------------
| Rute-rute ini hanya bisa diakses oleh user dengan role 'admin'.
| Menangani dashboard utama, rekapitulasi data, ekspor laporan, 
| serta manajemen data master (Master STO, Plant, Material, User, dll).
*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // --- DASHBOARD ADMIN ---
        // Halaman dashboard utama yang menampilkan metrik & statistik menyeluruh
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        // Polling aliran daftar data scan yang masuk secara realtime
        Route::get('/api/dashboard/latest-scan', [DashboardController::class, 'latestScanData'])->middleware('throttle:datatable')->name('api.dashboard.latest-scan');

        // --- MANAJEMEN HASIL SCAN (FULL CRUD) ---
        // Halaman tampilan tabel seluruh data Scan Result
        Route::get('/scan-results', [DashboardController::class, 'scanResults'])->name('scan-results');
        Route::get('/api/scan-results', [DashboardController::class, 'datatable'])->middleware('throttle:datatable')->name('api.scan-results');
        // Aksi admin menambah secara manual, mengedit paksa, dan menghapus data scan orang lain
        Route::post('/api/scan-results', [DashboardController::class, 'store'])->name('api.scan-results.store');
        Route::put('/api/scan-results/{id}', [DashboardController::class, 'update'])->name('api.scan-results.update');
        Route::delete('/api/scan-results/{id}', [DashboardController::class, 'destroy'])->name('api.scan-results.destroy');

        // --- REKAPITULASI MATERIAL ---
        // Halaman tabel yang mengakumulasi Qty berdasarkan tipe material
        Route::get('/material-summary', [DashboardController::class, 'materialSummary'])->name('material-summary');
        Route::get('/api/material-summary', [DashboardController::class, 'materialSummaryData'])->middleware('throttle:datatable')->name('api.material-summary');
        
        // --- EXPORT SCAN RESULTS ---
        // Mengantre export Excel/PDF secara Asynchronous (cocok untuk jumlah data besar >50.000 row)
        Route::post('/export/scan-results/{format}', [DashboardController::class, 'queueExport'])
            ->whereIn('format', ['excel', 'pdf'])
            ->middleware('throttle:export')
            ->name('export.scan-results.queue');
        Route::get('/export/scan-results/status', [DashboardController::class, 'exportStatus'])->name('export.scan-results.status');
        Route::get('/export/scan-results/{exportRequest}/download', [DashboardController::class, 'downloadExport'])->name('export.scan-results.download');
        
        // Export laporan langsung mem-blocking response PHP (synchronous stream)
        Route::get('/export/scan-results/excel', [DashboardController::class, 'exportExcel'])
            ->middleware('throttle:export')
            ->name('export.scan-results.excel');
        Route::get('/export/scan-results/pdf', [DashboardController::class, 'exportPdf'])
            ->middleware('throttle:export')
            ->name('export.scan-results.pdf');

        // --- MANAJEMEN DATA MASTER ---
        // Master STO (Kode Periode Kegiatan Stock Take)
        Route::get('/master-sto', [MasterController::class, 'sto'])->name('master-sto');
        Route::get('/api/master-sto', [MasterController::class, 'stoData'])->middleware('throttle:datatable')->name('api.master-sto');
        Route::post('/api/master-sto', [MasterController::class, 'storeSto'])->name('api.master-sto.store');
        Route::put('/api/master-sto/{id}', [MasterController::class, 'updateSto'])->name('api.master-sto.update');
        Route::post('/api/master-sto/{id}/activate', [MasterController::class, 'activateSto'])->name('api.master-sto.activate'); // Hanya 1 STO yang boleh aktif sekaligus
        Route::post('/api/master-sto/{id}/deactivate', [MasterController::class, 'deactivateSto'])->name('api.master-sto.deactivate');
        Route::delete('/api/master-sto/{id}', [MasterController::class, 'destroySto'])->name('api.master-sto.destroy');

        // Master Plant (Lokasi Gedung / Area Rak)
        Route::get('/master-plant', [MasterController::class, 'plants'])->name('master-plant');
        Route::get('/api/master-plant', [MasterController::class, 'plantData'])->middleware('throttle:datatable')->name('api.master-plant');
        Route::post('/api/master-plant', [MasterController::class, 'storePlant'])->name('api.master-plant.store');
        Route::put('/api/master-plant/{id}', [MasterController::class, 'updatePlant'])->name('api.master-plant.update');
        Route::delete('/api/master-plant/{id}', [MasterController::class, 'destroyPlant'])->name('api.master-plant.destroy');

        // Master Material (Katalog material resmi sebagai landasan data lookup)
        Route::get('/master-material', [MasterController::class, 'materials'])->name('master-material');
        Route::get('/api/master-material', [MasterController::class, 'materialData'])->middleware('throttle:datatable')->name('api.master-material');
        Route::post('/api/master-material', [MasterController::class, 'storeMaterial'])->name('api.master-material.store');
        Route::put('/api/master-material/{id}', [MasterController::class, 'updateMaterial'])->name('api.master-material.update');
        Route::delete('/api/master-material/{id}', [MasterController::class, 'destroyMaterial'])->name('api.master-material.destroy');

        // Master Keterangan (Daftar opsi kelainan/catatan yang akan masuk combo box scanner)
        Route::get('/master-keterangan', [MasterController::class, 'keterangan'])->name('master-keterangan');
        Route::get('/api/master-keterangan', [MasterController::class, 'keteranganData'])->middleware('throttle:datatable')->name('api.master-keterangan');
        Route::post('/api/master-keterangan', [MasterController::class, 'storeKeterangan'])->name('api.master-keterangan.store');
        Route::put('/api/master-keterangan/{id}', [MasterController::class, 'updateKeterangan'])->name('api.master-keterangan.update');
        Route::delete('/api/master-keterangan/{id}', [MasterController::class, 'destroyKeterangan'])->name('api.master-keterangan.destroy');

        // Manajemen User (Akun-akun pengguna aplikasi STO)
        Route::get('/users', [MasterController::class, 'users'])->name('users');
        Route::get('/api/users', [MasterController::class, 'userData'])->middleware('throttle:datatable')->name('api.users');
        Route::post('/api/users', [MasterController::class, 'storeUser'])->name('api.users.store');
        Route::put('/api/users/{id}', [MasterController::class, 'updateUser'])->name('api.users.update');
        Route::delete('/api/users/{id}', [MasterController::class, 'destroyUser'])->name('api.users.destroy');
    });
