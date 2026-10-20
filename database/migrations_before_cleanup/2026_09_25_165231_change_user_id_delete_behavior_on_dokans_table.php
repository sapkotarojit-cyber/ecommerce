<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokans', function (Blueprint $table) {
            // Remove the existing foreign key
            $table->dropForeign(['user_id']);
        });

        Schema::table('dokans', function (Blueprint $table) {
            // User can be deleted without deleting the Dokan.
            // The user_id will simply become NULL.
            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->change();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dokans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('dokans', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')
                ->nullable(false)
                ->change();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};