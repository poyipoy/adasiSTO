<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sto_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('sto_code', 50);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->string('pic', 255);
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();

            $table->index('user_id');
            $table->index('plant_id');
            $table->index('sto_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sto_sessions');
    }
};
