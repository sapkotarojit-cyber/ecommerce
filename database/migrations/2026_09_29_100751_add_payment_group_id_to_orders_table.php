<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'payment_group_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('payment_group_id', 150)
                    ->nullable()
                    ->index()
                    ->after('payment_transaction_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'payment_group_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex(['payment_group_id']);
                $table->dropColumn('payment_group_id');
            });
        }
    }
};