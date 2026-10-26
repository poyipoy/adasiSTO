<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
        ];

        $healthy = collect($checks)->every(fn (array $check) => $check['status'] === 'ok');

        return response()->json([
            'success' => $healthy,
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function checkApp(): array
    {
        $check = [
            'status' => 'ok',
            'name' => config('app.name'),
        ];

        if (config('sto.health_expose_environment', false)) {
            $check['environment'] = app()->environment();
        }

        return $check;
    }

    private function checkDatabase(): array
    {
        try {
            DB::select('select 1');

            return ['status' => 'ok'];
        } catch (Throwable) {
            return ['status' => 'failed'];
        }
    }

    private function checkStorage(): array
    {
        $path = storage_path();

        return is_dir($path) && is_readable($path) && is_writable($path)
            ? ['status' => 'ok']
            : ['status' => 'failed'];
    }
}
