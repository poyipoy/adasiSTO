<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barcode_requests', function (Blueprint $table) {
            $table->unsignedInteger('print_count')->default(1)->after('qty');
            $table->foreignId('printed_by_user_id')
                ->nullable()
                ->after('reviewed_at')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('printed_at')->nullable()->after('printed_by_user_id');

            $table->index(['status', 'printed_at'], 'barcode_requests_status_printed_idx');
        });

        DB::table('barcode_requests')->whereNull('qty')->update(['qty' => 1]);

        Schema::table('barcode_requests', function (Blueprint $table) {
            $table->unsignedInteger('qty')->default(1)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('barcode_requests', function (Blueprint $table) {
            $table->dropIndex('barcode_requests_status_printed_idx');
            $table->dropForeign(['printed_by_user_id']);
            $table->dropColumn(['print_count', 'printed_by_user_id', 'printed_at']);
            $table->unsignedInteger('qty')->nullable()->default(null)->change();
        });
    }
};
