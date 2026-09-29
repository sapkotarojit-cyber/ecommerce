<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Add missing order fields
        |--------------------------------------------------------------------------
        */

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number', 100)
                    ->unique()
                    ->after('id');
            }

            if (!Schema::hasColumn('orders', 'order_status')) {
                $table->string('order_status', 30)
                    ->default('pending')
                    ->after('status');
            }

            if (!Schema::hasColumn('orders', 'payment_receipt')) {
                $table->string('payment_receipt', 500)
                    ->nullable()
                    ->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'payment_transaction_id')) {
                $table->string('payment_transaction_id', 150)
                    ->nullable()
                    ->unique()
                    ->after('payment_receipt');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Convert payment_method from ENUM to string
        |--------------------------------------------------------------------------
        |
        | Your current migration only allows:
        |
        | cod, kalti
        |
        | But your checkout uses:
        |
        | cod, bank, esewa
        |
        | Using a string makes adding payment providers safer.
        |
        */

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 30)
                ->default('cod')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'tracking_number')) {
                $table->dropUnique(['tracking_number']);
                $table->dropColumn('tracking_number');
            }

            if (Schema::hasColumn('orders', 'order_status')) {
                $table->dropColumn('order_status');
            }

            if (Schema::hasColumn('orders', 'payment_receipt')) {
                $table->dropColumn('payment_receipt');
            }

            if (Schema::hasColumn('orders', 'payment_transaction_id')) {
                $table->dropUnique(['payment_transaction_id']);
                $table->dropColumn('payment_transaction_id');
            }

            $table->enum('payment_method', ['cod', 'kalti'])
                ->default('cod')
                ->change();
        });
    }
};