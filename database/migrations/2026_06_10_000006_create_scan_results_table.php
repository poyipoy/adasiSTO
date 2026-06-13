<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sto_code_id')->nullable()->constrained('sto_codes')->nullOnDelete();
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();

            $table->string('sto_code');

            $table->string('barcode_raw');
            $table->string('barcode_material');
            $table->string('lot_number');
            $table->unsignedInteger('qty')->default(1);

            $table->string('material_code');
            $table->string('material_name');

            $table->string('shape_code', 10);
            $table->string('shape_name', 50);

            $table->unsignedInteger('thickness')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('diameter')->nullable();
            $table->unsignedInteger('length')->nullable();

            $table->string('keterangan')->default('OK');
            $table->string('scan_source')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('sto_code_id');
            $table->index('plant_id');
            $table->index('location_id');
            $table->index('sto_code');
            $table->index('barcode_material');
            $table->index('material_code');
            $table->index('shape_code');
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
            $table->index(['sto_code', 'barcode_material']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};
