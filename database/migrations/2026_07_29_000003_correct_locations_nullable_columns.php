<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'old_location_name')) {
                $table->string('old_location_name', 100)->nullable()->change();
            }

            if (Schema::hasColumn('locations', 'description')) {
                $table->string('description', 255)->nullable()->change();
            }

            if (Schema::hasColumn('locations', 'warehouse')) {
                $table->string('warehouse', 100)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        // Intentionally irreversible: restoring NOT NULL would break valid production rows.
    }
};
