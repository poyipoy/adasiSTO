<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('barcode_requests', function (Blueprint $table) {
            // qty diisi admin saat generate, bukan saat scanner request
            $table->unsignedInteger('qty')->nullable()->after('lot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barcode_requests', function (Blueprint $table) {
            $table->dropColumn('qty');
        });
    }
};
