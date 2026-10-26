<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rack_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sto_code_id')->constrained('sto_codes')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('status', 20)->default('pending');
            $table->foreignId('finished_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('validated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->foreignId('confirmed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->unique(['sto_code_id', 'location_id'], 'rack_confirmations_sto_location_unique');
            $table->index(['sto_code_id', 'status'], 'rack_confirmations_sto_status_idx');
        });

        Schema::create('rack_confirmation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_confirmation_id')->constrained('rack_confirmations')->restrictOnDelete();
            $table->foreignId('sto_code_id')->constrained('sto_codes')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('action_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['sto_code_id', 'location_id', 'created_at'], 'rack_history_scope_idx');
            $table->index(['action_by_user_id', 'created_at'], 'rack_history_actor_idx');
        });

        $activeStoId = DB::table('sto_codes')->where('is_active', true)->value('id');

        if ($activeStoId === null) {
            return;
        }

        DB::table('locations')->orderBy('id')->chunkById(250, function ($locations) use ($activeStoId) {
            foreach ($locations as $location) {
                $isConfirmed = (bool) $location->is_confirmed;
                $confirmationId = DB::table('rack_confirmations')->insertGetId([
                    'sto_code_id' => $activeStoId,
                    'location_id' => $location->id,
                    'status' => $isConfirmed ? 'confirmed' : 'pending',
                    'confirmed_by_user_id' => $isConfirmed ? $location->confirmed_by_user_id : null,
                    'confirmed_at' => $isConfirmed ? $location->confirmed_at : null,
                    'note' => $isConfirmed ? $location->confirmation_note : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($isConfirmed) {
                    DB::table('rack_confirmation_histories')->insert([
                        'rack_confirmation_id' => $confirmationId,
                        'sto_code_id' => $activeStoId,
                        'location_id' => $location->id,
                        'from_status' => 'pending',
                        'to_status' => 'confirmed',
                        'action_by_user_id' => $location->confirmed_by_user_id,
                        'note' => $location->confirmation_note,
                        'metadata' => json_encode(['source' => 'legacy_backfill']),
                        'created_at' => $location->confirmed_at ?? now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rack_confirmation_histories');
        Schema::dropIfExists('rack_confirmations');
    }
};
