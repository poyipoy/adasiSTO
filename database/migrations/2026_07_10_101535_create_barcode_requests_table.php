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
        Schema::create('barcode_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sto_code_id')->nullable()->constrained('sto_codes')->nullOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('material_code');
            $table->string('material_name');
            $table->string('shape_code', 10);
            $table->string('shape_name', 50);
            $table->unsignedInteger('thickness')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('diameter')->nullable();
            $table->unsignedInteger('length')->nullable();
            $table->string('lot_number');
            $table->string('status')->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->string('generated_barcode_material')->nullable();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('plant_id');
            $table->index('location_id');
            $table->index('status');
            $table->index('material_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_requests');
    }
};
