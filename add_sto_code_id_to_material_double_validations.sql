-- Disable foreign key checks temporarily to allow index dropping
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Hapus unique index lama yang tidak memuat STO
ALTER TABLE `material_double_validations` 
DROP INDEX `material_double_unique_group`;

-- 2. Tambah kolom `sto_code_id` setelah kolom `id`
ALTER TABLE `material_double_validations` 
ADD COLUMN `sto_code_id` BIGINT UNSIGNED NULL AFTER `id`;

-- 3. Hubungkan foreign key `sto_code_id` ke tabel `sto_codes`
ALTER TABLE `material_double_validations`
ADD CONSTRAINT `material_double_validations_sto_code_id_foreign` 
FOREIGN KEY (`sto_code_id`) REFERENCES `sto_codes` (`id`) ON DELETE CASCADE;

-- 4. Pindahkan data STO lama dari tabel scan_results ke tabel material_double_validations
UPDATE `material_double_validations` mdv
INNER JOIN (
    SELECT barcode_material, plant_id, location_id, MAX(sto_code_id) as sto_code_id
    FROM scan_results
    GROUP BY barcode_material, plant_id, location_id
) sr ON 
    mdv.barcode_material = sr.barcode_material 
    AND mdv.plant_id = sr.plant_id 
    AND mdv.location_id = sr.location_id
SET mdv.sto_code_id = sr.sto_code_id
WHERE mdv.sto_code_id IS NULL;

-- 5. Isi data sisa (jika ada) dengan ID STO yang aktif
UPDATE `material_double_validations`
SET `sto_code_id` = (SELECT id FROM `sto_codes` WHERE `is_active` = 1 LIMIT 1)
WHERE `sto_code_id` IS NULL;

-- Jika tidak ada STO aktif, gunakan STO pertama sebagai fallback
UPDATE `material_double_validations`
SET `sto_code_id` = (SELECT id FROM `sto_codes` LIMIT 1)
WHERE `sto_code_id` IS NULL;

-- 6. Buat unique index baru yang menyertakan `sto_code_id`
ALTER TABLE `material_double_validations` 
ADD UNIQUE KEY `material_double_unique_group` (`sto_code_id`, `barcode_material`, `plant_id`, `location_id`);

-- Enable foreign key checks back
SET FOREIGN_KEY_CHECKS = 1;
