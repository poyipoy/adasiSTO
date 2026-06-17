<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_double_validations', function (Blueprint $table) {
            $table->id();
            $table->string('barcode_material');
            $table->foreignId('plant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->unique(['barcode_material', 'plant_id', 'location_id'], 'material_double_unique_group');
            $table->index('barcode_material');
            $table->index(['plant_id', 'location_id']);
            $table->index('validated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_double_validations');
    }
};
