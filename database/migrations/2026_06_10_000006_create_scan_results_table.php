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
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sto_session_id')->constrained('sto_sessions')->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('barcode_material', 100);
            $table->string('material_code', 10);
            $table->string('material_name', 100);
            $table->string('shape_code', 10);
            $table->string('shape_name', 50);
            $table->decimal('thickness', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('diameter', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->integer('qty')->default(1);
            $table->string('lot', 100)->nullable();
            $table->timestamp('scan_time')->useCurrent();
            $table->string('keterangan', 100)->default('OK');
            $table->timestamps();

            // Performance indexes
            $table->index('user_id');
            $table->index('sto_session_id');
            $table->index('plant_id');
            $table->index('location_id');
            $table->index('barcode_material');
            $table->index('material_code');
            $table->index(['user_id', 'sto_session_id']); // For user data isolation
            $table->index('created_at'); // For ordering
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};
