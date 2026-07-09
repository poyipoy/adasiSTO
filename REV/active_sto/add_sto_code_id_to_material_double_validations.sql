-- 1. Drop existing unique index
ALTER TABLE `material_double_validations` 
DROP INDEX `material_double_unique_group`;

-- 2. Add column `sto_code_id` after `id`
ALTER TABLE `material_double_validations` 
ADD COLUMN `sto_code_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 3. Add foreign key constraint to `sto_codes(id)` with ON DELETE CASCADE
ALTER TABLE `material_double_validations`
ADD CONSTRAINT `material_double_validations_sto_code_id_foreign` 
FOREIGN KEY (`sto_code_id`) REFERENCES `sto_codes` (`id`) ON DELETE CASCADE;

-- 4. Create the new unique index including `sto_code_id`
ALTER TABLE `material_double_validations` 
ADD UNIQUE KEY `material_double_unique_group` (`sto_code_id`, `barcode_material`, `plant_id`, `location_id`);
