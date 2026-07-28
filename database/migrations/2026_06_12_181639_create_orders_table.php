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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dokan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_address_id')->constrained()->cascadeOnDelete();
            $table->double('total_amount');
            $table->string('status')->default('pending');
            $table->enum('payment_method', ['cod', 'kalti'])->default('cod');
            $table->string('payment_status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
