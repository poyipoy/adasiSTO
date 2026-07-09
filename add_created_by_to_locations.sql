-- Migration: add created_by_user_id to locations table
-- Run this SQL once if `php artisan migrate` cannot be executed.
-- Idempotent: checks existence before adding column.

SET @col_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'locations'
      AND COLUMN_NAME = 'created_by_user_id'
);

-- Add column only if it does not already exist
SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE `locations` ADD COLUMN `created_by_user_id` BIGINT UNSIGNED NULL AFTER `is_active`, ADD CONSTRAINT `locations_created_by_user_id_foreign` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
    'SELECT 1 -- column already exists, skipping'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Record the migration in Laravel's migrations table
INSERT IGNORE INTO `migrations` (`migration`, `batch`)
SELECT '2026_07_01_000001_add_created_by_to_locations_table',
       COALESCE((SELECT MAX(`batch`) FROM `migrations`), 0) + 1
WHERE NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_07_01_000001_add_created_by_to_locations_table'
);
