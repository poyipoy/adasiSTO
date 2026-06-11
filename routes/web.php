<?php

use App\Http\Controllers\ScanController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterController;
use Illuminate\Support\Facades\Route;

// ─── Public ───
Route::get('/', function () {
    return redirect()->route('login');
});

// ─── Auth Routes ───
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (auth()->user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('scan.setup'));
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    })->name('login.store');
});

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

// ─── User (Scanner) Routes ───
Route::middleware('auth')->group(function () {
    // Setup STO session
    Route::get('/scan/setup', [ScanController::class, 'setup'])->name('scan.setup');
    Route::post('/scan/setup', [ScanController::class, 'storeSetup'])->name('scan.store-setup');

    // Scanner
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan', [ScanController::class, 'storeScan'])->name('scan.store');

    // End session
    Route::post('/scan/end-session', [ScanController::class, 'endSession'])->name('scan.end-session');

    // Scan Results
    Route::get('/scan/results', [ScanController::class, 'results'])->name('scan.results');
    Route::get('/scan/datatable', [ScanController::class, 'datatable'])->name('scan.datatable');
    Route::put('/scan/{id}/keterangan', [ScanController::class, 'updateKeterangan'])->name('scan.update-keterangan');

    // API endpoints
    Route::get('/api/locations/{plantId}', [ScanController::class, 'getLocations'])->name('api.locations');
    Route::post('/api/locations', [ScanController::class, 'storeLocation'])->name('api.locations.store');
    Route::post('/api/change-location', [ScanController::class, 'changeLocation'])->name('api.change-location');
});

// ─── Admin Routes ───
Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Scan Results Monitoring
        Route::get('/scan-results', [DashboardController::class, 'scanResults'])->name('scan-results');
        Route::get('/scan-results/datatable', [DashboardController::class, 'datatable'])->name('scan-results.datatable');
        Route::get('/scan-results/export', [DashboardController::class, 'export'])->name('scan-results.export');
        Route::get('/scan-results/{id}/edit', [DashboardController::class, 'edit'])->name('scan-results.edit');
        Route::put('/scan-results/{id}', [DashboardController::class, 'update'])->name('scan-results.update');
        Route::delete('/scan-results/{id}', [DashboardController::class, 'destroy'])->name('scan-results.destroy');

        // Barcode Overview
        Route::get('/barcode-overview', [DashboardController::class, 'barcodeOverview'])->name('barcode-overview');
        Route::get('/barcode-overview/datatable', [DashboardController::class, 'overviewDatatable'])->name('barcode-overview.datatable');

        // Master Data
        Route::get('/master/plants', [MasterController::class, 'plants'])->name('master.plants');
        Route::post('/master/plants', [MasterController::class, 'storePlant'])->name('master.plants.store');
        Route::put('/master/plants/{id}', [MasterController::class, 'updatePlant'])->name('master.plants.update');
        Route::delete('/master/plants/{id}', [MasterController::class, 'destroyPlant'])->name('master.plants.destroy');

        Route::get('/master/materials', [MasterController::class, 'materials'])->name('master.materials');
        Route::post('/master/materials', [MasterController::class, 'storeMaterial'])->name('master.materials.store');
        Route::put('/master/materials/{id}', [MasterController::class, 'updateMaterial'])->name('master.materials.update');
        Route::delete('/master/materials/{id}', [MasterController::class, 'destroyMaterial'])->name('master.materials.destroy');

        Route::get('/master/keterangan', [MasterController::class, 'keterangan'])->name('master.keterangan');
        Route::post('/master/keterangan', [MasterController::class, 'storeKeterangan'])->name('master.keterangan.store');
        Route::put('/master/keterangan/{id}', [MasterController::class, 'updateKeterangan'])->name('master.keterangan.update');
        Route::delete('/master/keterangan/{id}', [MasterController::class, 'destroyKeterangan'])->name('master.keterangan.destroy');
    });
