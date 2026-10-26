<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GenerateBarcodeAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless(
            $user && $user->is_active && ($user->isAdmin() || ($user->isScanner() && $user->is_validator)),
            403,
            'Anda tidak memiliki akses ke Generate Barcode.',
        );

        return $next($request);
    }
}
