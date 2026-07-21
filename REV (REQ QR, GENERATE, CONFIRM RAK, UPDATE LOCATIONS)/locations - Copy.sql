ALTER TABLE `locations`
    ADD COLUMN `old_location_name` VARCHAR(100) NULL AFTER `name`,
    ADD COLUMN `description` VARCHAR(255) NULL AFTER `old_location_name`,
    ADD COLUMN `warehouse` VARCHAR(100) NULL AFTER `description`,
    ADD COLUMN `is_confirmed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`,
    ADD COLUMN `confirmed_by_user_id` BIGINT UNSIGNED NULL AFTER `is_confirmed`,
    ADD COLUMN `confirmed_at` TIMESTAMP NULL AFTER `confirmed_by_user_id`,
    ADD COLUMN `confirmation_note` VARCHAR(500) NULL AFTER `confirmed_at`,
    ADD CONSTRAINT `locations_confirmed_by_user_id_foreign` FOREIGN KEY (`confirmed_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL;
