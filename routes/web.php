<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\MaterialDoubleController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\ScanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class)->name('health');

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->to(route('login', [], false));
    }

    return Auth::user()->isAdmin()
        ? redirect()->to(route('admin.dashboard', [], false))
        : redirect()->to(route('scan.setup', [], false));
});

Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');

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
            ? redirect()->intended(route('admin.dashboard', [], false))
            : redirect()->intended(route('scan.setup', [], false));
    })->middleware('throttle:login')->name('login.store');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->to(route('login', [], false));
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:scanner'])->group(function () {
    Route::get('/scan/overview', [ScanController::class, 'overview'])->name('scan.overview');
    Route::get('/api/scan/overview', [ScanController::class, 'overviewData'])->middleware('throttle:datatable')->name('api.scan.overview');
    Route::get('/scan/setup', [ScanController::class, 'setup'])->name('scan.setup');
    Route::post('/scan/setup', [ScanController::class, 'storeSetup'])->name('scan.setup.store');
    Route::get('/scan/scanner', [ScanController::class, 'scanner'])->name('scan.scanner');
    Route::get('/scan/history', [ScanController::class, 'historyPage'])->name('scan.history');
    Route::get('/scan/material-summary', [ScanController::class, 'materialSummary'])->name('scan.material-summary');

    Route::get('/api/locations', [ScanController::class, 'locations'])->middleware('throttle:datatable')->name('api.locations');
    Route::post('/api/locations', [ScanController::class, 'storeLocation'])->middleware('throttle:scan-write')->name('api.locations.store');
    Route::delete('/api/locations/{id}', [ScanController::class, 'destroyLocation'])->middleware('throttle:scan-write')->name('api.locations.destroy');
    Route::post('/api/scan/preview', [ScanController::class, 'preview'])->middleware('throttle:scan-write')->name('api.scan.preview');
    Route::post('/api/scan/check-duplicate', [ScanController::class, 'checkDuplicate'])->middleware('throttle:scan-write')->name('api.scan.check-duplicate');
    Route::post('/api/scan/store', [ScanController::class, 'store'])->middleware('throttle:scan-write')->name('api.scan.store');
    Route::get('/api/scan/recent', [ScanController::class, 'recent'])->middleware('throttle:datatable')->name('api.scan.recent');
    Route::get('/api/scan/history', [ScanController::class, 'history'])->middleware('throttle:datatable')->name('api.scan.history');
    Route::get('/api/scan/material-summary', [ScanController::class, 'materialSummaryData'])->middleware('throttle:datatable')->name('api.scan.material-summary');
    Route::delete('/api/scan/{id}', [ScanController::class, 'destroy'])->middleware('throttle:scan-write')->name('api.scan.destroy');
});

Route::middleware(['auth', 'material-double-access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/material-double', [MaterialDoubleController::class, 'index'])->name('material-double');
        Route::get('/api/material-double', [MaterialDoubleController::class, 'datatable'])->middleware('throttle:datatable')->name('api.material-double');
        Route::get('/api/material-double/detail', [MaterialDoubleController::class, 'showDuplicateDetail'])->middleware('throttle:datatable')->name('api.material-double.detail');
        Route::post('/api/material-double/validate', [MaterialDoubleController::class, 'validateDuplicate'])->name('api.material-double.validate');
        Route::post('/api/material-double/scan', [MaterialDoubleController::class, 'scan'])
            ->middleware('throttle:scan-write')
            ->name('api.material-double.scan');
        Route::delete('/api/material-double/delete-selected', [MaterialDoubleController::class, 'deleteSelected'])->name('api.material-double.delete-selected');
        Route::post('/api/material-double/export', [MaterialDoubleController::class, 'queueExport'])
            ->middleware('throttle:export')
            ->name('api.material-double.export.queue');
        Route::get('/api/material-double/export/status', [MaterialDoubleController::class, 'exportStatus'])->name('api.material-double.export.status');
        Route::get('/api/material-double/export/{exportRequest}/download', [MaterialDoubleController::class, 'downloadExport'])->name('api.material-double.export.download');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/api/dashboard/latest-scan', [DashboardController::class, 'latestScanData'])->middleware('throttle:datatable')->name('api.dashboard.latest-scan');

        Route::get('/scan-results', [DashboardController::class, 'scanResults'])->name('scan-results');
        Route::get('/api/scan-results', [DashboardController::class, 'datatable'])->middleware('throttle:datatable')->name('api.scan-results');
        Route::post('/api/scan-results', [DashboardController::class, 'store'])->name('api.scan-results.store');
        Route::put('/api/scan-results/{id}', [DashboardController::class, 'update'])->name('api.scan-results.update');
        Route::delete('/api/scan-results/{id}', [DashboardController::class, 'destroy'])->name('api.scan-results.destroy');

        Route::get('/material-summary', [DashboardController::class, 'materialSummary'])->name('material-summary');
        Route::get('/api/material-summary', [DashboardController::class, 'materialSummaryData'])->middleware('throttle:datatable')->name('api.material-summary');
        Route::post('/export/scan-results/{format}', [DashboardController::class, 'queueExport'])
            ->whereIn('format', ['excel', 'pdf'])
            ->middleware('throttle:export')
            ->name('export.scan-results.queue');
        Route::get('/export/scan-results/status', [DashboardController::class, 'exportStatus'])->name('export.scan-results.status');
        Route::get('/export/scan-results/{exportRequest}/download', [DashboardController::class, 'downloadExport'])->name('export.scan-results.download');
        Route::get('/export/scan-results/excel', [DashboardController::class, 'exportExcel'])
            ->middleware('throttle:export')
            ->name('export.scan-results.excel');
        Route::get('/export/scan-results/pdf', [DashboardController::class, 'exportPdf'])
            ->middleware('throttle:export')
            ->name('export.scan-results.pdf');

        Route::get('/master-sto', [MasterController::class, 'sto'])->name('master-sto');
        Route::get('/api/master-sto', [MasterController::class, 'stoData'])->middleware('throttle:datatable')->name('api.master-sto');
        Route::post('/api/master-sto', [MasterController::class, 'storeSto'])->name('api.master-sto.store');
        Route::put('/api/master-sto/{id}', [MasterController::class, 'updateSto'])->name('api.master-sto.update');
        Route::post('/api/master-sto/{id}/activate', [MasterController::class, 'activateSto'])->name('api.master-sto.activate');
        Route::post('/api/master-sto/{id}/deactivate', [MasterController::class, 'deactivateSto'])->name('api.master-sto.deactivate');
        Route::delete('/api/master-sto/{id}', [MasterController::class, 'destroySto'])->name('api.master-sto.destroy');

        Route::get('/master-plant', [MasterController::class, 'plants'])->name('master-plant');
        Route::get('/api/master-plant', [MasterController::class, 'plantData'])->middleware('throttle:datatable')->name('api.master-plant');
        Route::post('/api/master-plant', [MasterController::class, 'storePlant'])->name('api.master-plant.store');
        Route::put('/api/master-plant/{id}', [MasterController::class, 'updatePlant'])->name('api.master-plant.update');
        Route::delete('/api/master-plant/{id}', [MasterController::class, 'destroyPlant'])->name('api.master-plant.destroy');

        Route::get('/master-material', [MasterController::class, 'materials'])->name('master-material');
        Route::get('/api/master-material', [MasterController::class, 'materialData'])->middleware('throttle:datatable')->name('api.master-material');
        Route::post('/api/master-material', [MasterController::class, 'storeMaterial'])->name('api.master-material.store');
        Route::put('/api/master-material/{id}', [MasterController::class, 'updateMaterial'])->name('api.master-material.update');
        Route::delete('/api/master-material/{id}', [MasterController::class, 'destroyMaterial'])->name('api.master-material.destroy');

        Route::get('/master-keterangan', [MasterController::class, 'keterangan'])->name('master-keterangan');
        Route::get('/api/master-keterangan', [MasterController::class, 'keteranganData'])->middleware('throttle:datatable')->name('api.master-keterangan');
        Route::post('/api/master-keterangan', [MasterController::class, 'storeKeterangan'])->name('api.master-keterangan.store');
        Route::put('/api/master-keterangan/{id}', [MasterController::class, 'updateKeterangan'])->name('api.master-keterangan.update');
        Route::delete('/api/master-keterangan/{id}', [MasterController::class, 'destroyKeterangan'])->name('api.master-keterangan.destroy');

        Route::get('/users', [MasterController::class, 'users'])->name('users');
        Route::get('/api/users', [MasterController::class, 'userData'])->middleware('throttle:datatable')->name('api.users');
        Route::post('/api/users', [MasterController::class, 'storeUser'])->name('api.users.store');
        Route::put('/api/users/{id}', [MasterController::class, 'updateUser'])->name('api.users.update');
        Route::delete('/api/users/{id}', [MasterController::class, 'destroyUser'])->name('api.users.destroy');
    });
