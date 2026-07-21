<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => $this->checkApp(),
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
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

    private function checkQueue(): array
    {
        try {
            $connection = config('queue.default');
            $configExists = is_array(config("queue.connections.{$connection}"));
            $driver = config("queue.connections.{$connection}.driver");

            if (!$configExists) {
                return ['status' => 'failed', 'connection' => $connection];
            }

            if ($driver === 'database' && !Schema::hasTable(config("queue.connections.{$connection}.table", 'jobs'))) {
                return ['status' => 'failed', 'connection' => $connection, 'driver' => $driver];
            }

            return ['status' => 'ok', 'connection' => $connection, 'driver' => $driver];
        } catch (Throwable) {
            return ['status' => 'failed'];
        }
    }
}
