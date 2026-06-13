<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_materials', function (Blueprint $table) {
            $table->id();
            $table->string('material_code')->unique();
            $table->string('material_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('material_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_materials');
    }
};
