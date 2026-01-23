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
        Schema::table('users', function (Blueprint $table) {
            $table->index('points', 'idx_points');
            $table->index('is_suspended', 'idx_suspended');
        });

        Schema::table('attendances', function (Blueprint $table) {
            // Speed up date range filtering for statistics
            $table->index(['created_at', 'division_id'], 'idx_date_div');
        });

        Schema::table('cash_logs', function (Blueprint $table) {
            // Speed up debt calculations
            $table->index(['status', 'user_id', 'date'], 'idx_status_user_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_points');
            $table->dropIndex('idx_suspended');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_date_div');
        });

        Schema::table('cash_logs', function (Blueprint $table) {
            $table->dropIndex('idx_status_user_date');
        });
    }
};
