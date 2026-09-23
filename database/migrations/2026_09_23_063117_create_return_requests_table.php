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
        Schema::create('return_requests', function (Blueprint $table) {
            $table->id();

            // The order being returned
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Customer who requested the return
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Vendor/store
            $table->foreignId('dokan_id')
                ->constrained('dokans')
                ->cascadeOnDelete();

            // Customer's reason for returning the order
            $table->text('reason')->nullable();

            // requested, approved, rejected, received, completed
            $table->string('status')->default('requested');

            // pending, processing, completed, failed
            $table->string('refund_status')->default('pending');

            // Amount that will actually be refunded
            $table->double('refund_amount')->nullable();

            // Admin/vendor notes
            $table->text('admin_note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_requests');
    }
};