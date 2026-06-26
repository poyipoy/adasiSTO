<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Performance audit — missing composite indexes.
 *
 * Findings:
 *   - duplicateGroupQuery / isDuplicate groupBy(barcode_material, plant_id, location_id)
 *   - overview filters: sto_code + plant_id + created_at
 *   - overview counts: keterangan (valid / invalid)
 *   - export request polling: user_id + report + created_at
 *   - whereHas('location') filter by name
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            // Duplicate detection & Material Double GROUP BY
            $table->index(
                ['barcode_material', 'plant_id', 'location_id'],
                'scan_results_barcode_plant_location_idx'
            );

            // Overview & export filter pattern: sto_code + plant_id + created_at
            $table->index(
                ['sto_code', 'plant_id', 'created_at'],
                'scan_results_sto_plant_created_idx'
            );

            // Overview count by keterangan (valid/invalid)
            $table->index('keterangan', 'scan_results_keterangan_idx');
        });

        Schema::table('export_requests', function (Blueprint $table) {
            // Recent exports polling: user_id + report + created_at (desc)
            $table->index(
                ['user_id', 'report', 'created_at'],
                'export_requests_user_report_created_idx'
            );
        });

        if (! $this->hasIndex('locations', 'locations_name_idx')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->index('name', 'locations_name_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->dropIndex('scan_results_barcode_plant_location_idx');
            $table->dropIndex('scan_results_sto_plant_created_idx');
            $table->dropIndex('scan_results_keterangan_idx');
        });

        Schema::table('export_requests', function (Blueprint $table) {
            $table->dropIndex('export_requests_user_report_created_idx');
        });

        if ($this->hasIndex('locations', 'locations_name_idx')) {
            Schema::table('locations', function (Blueprint $table) {
                $table->dropIndex('locations_name_idx');
            });
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getIndexes($table);

        foreach ($indexes as $existingIndex) {
            if ($existingIndex['name'] === $index) {
                return true;
            }
        }

        return false;
    }
};
