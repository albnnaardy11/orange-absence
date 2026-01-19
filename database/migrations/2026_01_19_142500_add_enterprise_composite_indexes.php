<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'schedule_id'], 'idx_user_schedule');
            $table->index(['division_id', 'status'], 'idx_div_status');
            $table->index(['created_at', 'status'], 'idx_date_status');
        });

        Schema::table('cash_logs', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'idx_user_status');
            $table->index(['date', 'status'], 'idx_date_status');
            $table->index(['division_id', 'status'], 'idx_div_status');
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            $table->index(['division_id', 'is_active'], 'idx_div_active');
            $table->index(['code', 'is_active'], 'idx_code_active');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index(['division_id', 'status', 'day'], 'idx_div_status_day');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('idx_user_schedule');
            $table->dropIndex('idx_div_status');
            $table->dropIndex('idx_date_status');
        });

        Schema::table('cash_logs', function (Blueprint $table) {
            $table->dropIndex('idx_user_status');
            $table->dropIndex('idx_date_status');
            $table->dropIndex('idx_div_status');
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            $table->dropIndex('idx_div_active');
            $table->dropIndex('idx_code_active');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_div_status_day');
        });
    }
};
