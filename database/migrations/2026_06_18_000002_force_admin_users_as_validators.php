<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'admin')
            ->update(['is_validator' => true]);
    }

    public function down(): void
    {
        // Intentionally keep admin validator state on rollback.
    }
};
