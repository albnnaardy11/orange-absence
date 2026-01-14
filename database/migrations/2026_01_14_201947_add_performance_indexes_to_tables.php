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
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('status');
            $table->index('day');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('cash_logs', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            $table->index('date');
            $table->index('expires_at');
        });

        Schema::table('division_user', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['day']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('cash_logs', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['expires_at']);
        });

        Schema::table('division_user', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
    }
};
