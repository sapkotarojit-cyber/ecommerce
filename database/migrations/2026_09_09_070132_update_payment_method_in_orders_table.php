<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Option A: Change to standard string/VARCHAR(50)
            $table->string('payment_method', 50)->change();

            // OR Option B: Update ENUM values if you are using ENUM
            // $table->enum('payment_method', ['cod', 'online', 'card', 'bank_transfer'])->default('cod')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 10)->change();
        });
    }
};