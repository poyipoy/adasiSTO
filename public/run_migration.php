<?php
/**
 * Browser-accessible migration runner.
 * Visit: http://adasi_sto_test.test/run_migration.php
 * DELETE this file after running the migration.
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

header('Content-Type: text/plain; charset=UTF-8');

$migrationName = '2026_07_01_000001_add_created_by_to_locations_table';

try {
    $db = \Illuminate\Support\Facades\DB::connection();

    // 1. Check if column already exists
    $exists = $db->select("
        SELECT COUNT(*) as cnt
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'locations'
          AND COLUMN_NAME = 'created_by_user_id'
    ");
    $columnExists = (int) ($exists[0]->cnt ?? 0) > 0;

    if ($columnExists) {
        echo "✅ Column 'created_by_user_id' already exists in 'locations' table.\n";
    } else {
        // 2. Add column
        $db->statement("
            ALTER TABLE `locations`
            ADD COLUMN `created_by_user_id` BIGINT UNSIGNED NULL AFTER `is_active`,
            ADD CONSTRAINT `locations_created_by_user_id_foreign`
                FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ");
        echo "✅ Column 'created_by_user_id' added to 'locations' table.\n";
    }

    // 3. Record migration in Laravel migrations table
    $alreadyRecorded = $db->select("
        SELECT COUNT(*) as cnt FROM `migrations` WHERE `migration` = ?
    ", [$migrationName]);
    $recorded = (int) ($alreadyRecorded[0]->cnt ?? 0) > 0;

    if (!$recorded) {
        $maxBatch = $db->select("SELECT COALESCE(MAX(`batch`), 0) as b FROM `migrations`");
        $batch = (int) ($maxBatch[0]->b ?? 0) + 1;
        $db->table('migrations')->insert([
            'migration' => $migrationName,
            'batch'     => $batch,
        ]);
        echo "✅ Migration recorded in 'migrations' table (batch {$batch}).\n";
    } else {
        echo "ℹ️  Migration already recorded in 'migrations' table.\n";
    }

    echo "\n✅ DONE. The PIC column is now active.\n";
    echo "   You can delete this file: public/run_migration.php\n";

} catch (\Throwable $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString();
}
