<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

try {
    $sql = file_get_contents(__DIR__.'/../locations_insert.sql');
    $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
    
    // As a fallback for updating, let's also append ON DUPLICATE KEY UPDATE if they want it explicitly updated
    $sql = trim($sql);
    if (substr($sql, -1) === ';') {
        $sql = substr($sql, 0, -1);
    }
    $sql .= ' ON DUPLICATE KEY UPDATE is_active=VALUES(is_active), updated_at=VALUES(updated_at);';

    Illuminate\Support\Facades\DB::unprepared($sql);
    echo "SUCCESS: " . strlen($sql) . " bytes processed.";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
