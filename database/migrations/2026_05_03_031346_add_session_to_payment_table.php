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
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_session_id')
                ->nullable()
                ->after('order_id')
                ->constrained('order_sessions')
                ->nullOnDelete();

            $table->foreignId('collected_by')
                ->nullable()
                ->after('order_session_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('notes')->nullable()->after('reference');

            $table->index(['company_id', 'status']);
            $table->index('order_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_session_id']);
            $table->dropForeign(['collected_by']);
            $table->dropColumn(['order_session_id', 'collected_by', 'notes']);
        });
    }
};
