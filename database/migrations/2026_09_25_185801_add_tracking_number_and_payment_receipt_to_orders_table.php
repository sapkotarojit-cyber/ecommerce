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
        Schema::table('orders', function (Blueprint $table) {

            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number')
                    ->unique()
                    ->nullable()
                    ->after('shipping_address_id');
            }

            if (!Schema::hasColumn('orders', 'payment_receipt')) {
                $table->string('payment_receipt')
                    ->nullable()
                    ->after('payment_status');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            if (Schema::hasColumn('orders', 'tracking_number')) {
                $table->dropColumn('tracking_number');
            }

            if (Schema::hasColumn('orders', 'payment_receipt')) {
                $table->dropColumn('payment_receipt');
            }

        });
    }
};