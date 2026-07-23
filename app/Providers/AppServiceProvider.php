<?php

namespace App\Providers;

use App\Models\ScanResult;
use App\Policies\ScanResultPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ScanResult::class, ScanResultPolicy::class);

        RateLimiter::for('login', function (Request $request) {
            $username = strtolower((string) $request->input('username', 'guest'));

            return Limit::perMinute(max((int) config('sto.rate_limits.login_per_minute', 5), 1))
                ->by("login:{$username}|{$request->ip()}");
        });

        RateLimiter::for('scan-write', fn(Request $request) => Limit::perMinute(max((int) config('sto.rate_limits.scan_write_per_minute', 120), 1))
            ->by($this->rateLimitKey($request, 'scan-write')));

        RateLimiter::for('export', fn(Request $request) => Limit::perMinute(max((int) config('sto.rate_limits.export_per_minute', 10), 1))
            ->by($this->rateLimitKey($request, 'export')));

        RateLimiter::for('datatable', fn(Request $request) => Limit::perMinute(max((int) config('sto.rate_limits.datatable_per_minute', 240), 1))
            ->by($this->rateLimitKey($request, 'datatable')));
    }

    private function rateLimitKey(Request $request, string $bucket): string
    {
        return $bucket . ':' . ($request->user()?->getAuthIdentifier() ?: $request->ip());
    }
}
