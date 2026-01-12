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
        Schema::table('cash_logs', function (Blueprint $table) {
            $table->foreignId('attendance_id')->nullable()->change();
            $table->foreignId('division_id')->nullable()->change();
            $table->date('date')->nullable()->after('division_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_logs', function (Blueprint $table) {
            $table->foreignId('attendance_id')->nullable(false)->change();
            $table->foreignId('division_id')->nullable(false)->change();
            $table->dropColumn('date');
        });
    }
};
