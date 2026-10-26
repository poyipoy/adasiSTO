<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barcode_delete_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_scan_result_id')->nullable();
            $table->foreignId('sto_code_id')->nullable()->constrained('sto_codes')->nullOnDelete();
            $table->string('sto_code')->nullable();
            $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('deleted_by_name')->nullable();
            $table->foreignId('original_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_user_name')->nullable();
            $table->foreignId('plant_id')->nullable()->constrained('plants')->nullOnDelete();
            $table->string('plant_name')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('location_name')->nullable();
            $table->text('barcode_raw')->nullable();
            $table->string('barcode_material')->nullable();
            $table->string('material_code')->nullable();
            $table->string('material_name')->nullable();
            $table->string('shape_code', 10)->nullable();
            $table->string('shape_name', 50)->nullable();
            $table->unsignedInteger('thickness')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('diameter')->nullable();
            $table->unsignedInteger('length')->nullable();
            $table->string('lot_number')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->string('keterangan')->nullable();
            $table->string('scan_source')->nullable();
            $table->string('deletion_source', 50);
            $table->string('deletion_reason', 500)->nullable();
            $table->json('snapshot');
            $table->timestamp('original_scanned_at')->nullable();
            $table->timestamp('deleted_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index('original_scan_result_id');
            $table->index(['barcode_material', 'deleted_at'], 'barcode_delete_barcode_date_idx');
            $table->index(['material_code', 'deleted_at'], 'barcode_delete_material_date_idx');
            $table->index(['plant_id', 'location_id'], 'barcode_delete_location_idx');
            $table->index(['deleted_by_user_id', 'deleted_at'], 'barcode_delete_actor_date_idx');
            $table->index(['original_user_id', 'deleted_at'], 'barcode_delete_owner_date_idx');
            $table->index(['deletion_source', 'deleted_at'], 'barcode_delete_source_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcode_delete_histories');
    }
};
