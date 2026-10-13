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
            $table->string('password_reset_code', 6)
                ->nullable()
                ->after('password');

            $table->timestamp('password_reset_code_expires_at')
                ->nullable()
                ->after('password_reset_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'password_reset_code',
                'password_reset_code_expires_at',
            ]);
        });
    }
};