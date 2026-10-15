<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 254)->unique();
            $table->string('password');
            $table->string('verification_code_hash');
            $table->timestamp('verification_code_expires_at');
            $table->unsignedSmallInteger('verification_attempts')->default(0);
            $table->timestamp('last_code_sent_at')->nullable();
            $table->timestamps();

            $table->index('verification_code_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_registrations');
    }
};
