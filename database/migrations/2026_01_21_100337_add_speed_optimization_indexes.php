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
        Schema::table('attendances', function (Blueprint $table) {
            // Speed up duplicate checks and history listing
            $table->index(['user_id', 'division_id', 'created_at'], 'idx_user_div_date_v2');
            $table->index(['division_id', 'created_at'], 'idx_div_date_v2');
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            // Speed up code validation
            $table->index(['division_id', 'date', 'is_active'], 'idx_div_date_active_v2');
        });

        Schema::table('division_user', function (Blueprint $table) {
            // Speed up user division membership checks
            $table->index(['user_id', 'division_id'], 'idx_user_division_v2');
        });
        
        Schema::table('cash_logs', function (Blueprint $table) {
            // Speed up unpaid cash stats
            $table->index(['user_id', 'status', 'amount'], 'idx_user_status_amt_v2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_user_div_date_v2');
            $table->dropIndex('idx_div_date_v2');
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            $table->dropIndex('idx_div_date_active_v2');
        });

        Schema::table('division_user', function (Blueprint $table) {
            $table->dropIndex('idx_user_division_v2');
        });

        Schema::table('cash_logs', function (Blueprint $table) {
            $table->dropIndex('idx_user_status_amt_v2');
        });
    }
};
