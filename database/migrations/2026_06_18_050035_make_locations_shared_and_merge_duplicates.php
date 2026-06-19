<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Merge duplicate locations
        $duplicates = DB::table('locations')
            ->select('plant_id', 'name')
            ->groupBy('plant_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $locations = DB::table('locations')
                ->where('plant_id', $duplicate->plant_id)
                ->where('name', $duplicate->name)
                ->orderBy('id', 'asc')
                ->get();

            $primaryId = $locations->first()->id;
            $duplicateIds = $locations->skip(1)->pluck('id')->toArray();

            // Update foreign keys in scan_results
            DB::table('scan_results')
                ->whereIn('location_id', $duplicateIds)
                ->update(['location_id' => $primaryId]);

            // Update foreign keys in material_double_validations
            DB::table('material_double_validations')
                ->whereIn('location_id', $duplicateIds)
                ->update(['location_id' => $primaryId]);

            // Delete the duplicate locations
            DB::table('locations')
                ->whereIn('id', $duplicateIds)
                ->delete();
        }

        // 2. Modify schema
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'plant_id', 'name']);
            $table->dropColumn('user_id');

            $table->unique(['plant_id', 'name'], 'locations_plant_id_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique('locations_plant_id_name_unique');
            
            // Assume system user or admin (ID 1) as the default user_id for rollback
            $table->foreignId('user_id')->default(1)->constrained()->cascadeOnDelete();
            
            $table->unique(['user_id', 'plant_id', 'name']);
        });
    }
};
