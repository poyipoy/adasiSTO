<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_result_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_result_id')->constrained('scan_results')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['created', 'updated', 'deleted']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index('scan_result_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_result_logs');
    }
};
