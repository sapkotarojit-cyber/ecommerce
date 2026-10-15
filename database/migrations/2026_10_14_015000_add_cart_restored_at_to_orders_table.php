<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'cart_restored_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('cart_restored_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'cart_restored_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('cart_restored_at');
            });
        }
    }
};
