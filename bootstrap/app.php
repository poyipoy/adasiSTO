<?php

use App\Http\Middleware\GenerateBarcodeAccessMiddleware;
use App\Http\Middleware\MaterialDoubleAccessMiddleware;
use App\Http\Middleware\RackConfirmationAccessMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'material-double-access' => MaterialDoubleAccessMiddleware::class,
            'generate-barcode-access' => GenerateBarcodeAccessMiddleware::class,
            'rack-confirmation-access' => RackConfirmationAccessMiddleware::class,
        ]);

        $middleware->redirectUsersTo(function (Request $request): string {
            return $request->user()?->isAdmin()
                ? route('admin.dashboard')
                : route('scan.setup');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
