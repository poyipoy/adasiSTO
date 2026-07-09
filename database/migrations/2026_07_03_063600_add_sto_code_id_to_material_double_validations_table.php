<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('material_double_validations', function (Blueprint $table) {
            $table->dropUnique('material_double_unique_group');
        });

        Schema::table('material_double_validations', function (Blueprint $table) {
            $table->foreignId('sto_code_id')->nullable()->after('id')->constrained('sto_codes')->cascadeOnDelete();
        });

        // Populate sto_code_id from scan_results matching barcode, plant, and location
        \Illuminate\Support\Facades\DB::statement("
            UPDATE material_double_validations mdv
            INNER JOIN (
                SELECT barcode_material, plant_id, location_id, MAX(sto_code_id) as sto_code_id
                FROM scan_results
                GROUP BY barcode_material, plant_id, location_id
            ) sr ON 
                mdv.barcode_material = sr.barcode_material 
                AND mdv.plant_id = sr.plant_id 
                AND mdv.location_id = sr.location_id
            SET mdv.sto_code_id = sr.sto_code_id
            WHERE mdv.sto_code_id IS NULL
        ");

        // For any remaining orphans, assign them to the current active STO (or first STO as fallback)
        $activeStoId = \Illuminate\Support\Facades\DB::table('sto_codes')->where('is_active', true)->value('id')
            ?? \Illuminate\Support\Facades\DB::table('sto_codes')->value('id');

        if ($activeStoId) {
            \Illuminate\Support\Facades\DB::table('material_double_validations')
                ->whereNull('sto_code_id')
                ->update(['sto_code_id' => $activeStoId]);
        }

        Schema::table('material_double_validations', function (Blueprint $table) {
            $table->unique(['sto_code_id', 'barcode_material', 'plant_id', 'location_id'], 'material_double_unique_group');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('material_double_validations', function (Blueprint $table) {
            $table->dropUnique('material_double_unique_group');
            $table->dropForeign(['sto_code_id']);
            $table->dropColumn('sto_code_id');
            
            $table->unique(['barcode_material', 'plant_id', 'location_id'], 'material_double_unique_group');
        });

        Schema::enableForeignKeyConstraints();
    }
};
