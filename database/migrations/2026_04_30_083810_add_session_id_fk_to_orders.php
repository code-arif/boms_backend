<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('session_id');
            $table->foreignId('order_session_id')
                ->nullable()
                ->after('user_id')
                ->constrained('order_sessions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['order_session_id']);
            $table->dropColumn('order_session_id');
            $table->string('session_id')->nullable()->index();
        });
    }
};
