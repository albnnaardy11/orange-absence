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
        Schema::table('verification_codes', function (Blueprint $table) {
            $table->date('date')->nullable()->after('code');
            $table->boolean('is_active')->default(true)->after('date');
            $table->renameColumn('expired_at', 'expires_at');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('verification_code_id')->nullable()->after('division_id')->constrained()->nullOnDelete();
            $table->string('status')->default('hadir')->change(); // Use string for enum flexibility
            $table->dropColumn('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['verification_code_id']);
            $table->dropColumn('verification_code_id');
            $table->boolean('status')->default(false)->change();
            $table->timestamp('verified_at')->nullable();
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            $table->renameColumn('expires_at', 'expired_at');
            $table->dropColumn(['date', 'is_active']);
        });
    }
};
