<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->index('lot_number', 'scan_results_lot_number_idx');
            $table->index(['user_id', 'sto_code_id', 'created_at'], 'scan_results_user_sto_id_created_idx');
            $table->index(['user_id', 'sto_code', 'created_at'], 'scan_results_user_sto_code_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->dropIndex('scan_results_lot_number_idx');
            $table->dropIndex('scan_results_user_sto_id_created_idx');
            $table->dropIndex('scan_results_user_sto_code_created_idx');
        });
    }
};
