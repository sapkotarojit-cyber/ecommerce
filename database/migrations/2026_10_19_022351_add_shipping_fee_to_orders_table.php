<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'shipping_fee')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('shipping_fee', 10, 2)
                    ->default(0)
                    ->after('shipping_address_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'shipping_fee')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('shipping_fee');
            });
        }
    }
};
