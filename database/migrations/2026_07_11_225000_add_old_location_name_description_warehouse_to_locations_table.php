<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('old_location_name', 100)->nullable()->after('name');
            $table->string('description', 255)->nullable()->after('old_location_name');
            $table->string('warehouse', 100)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['old_location_name', 'description', 'warehouse']);
        });
    }
};
