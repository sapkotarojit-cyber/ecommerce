<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign([
                'product_id',
            ]);

            $table->dropForeign([
                'varient_id',
            ]);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->restrictOnDelete();

            $table->foreign('varient_id')
                ->references('id')
                ->on('product_varients')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign([
                'product_id',
            ]);

            $table->dropForeign([
                'varient_id',
            ]);

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete();

            $table->foreign('varient_id')
                ->references('id')
                ->on('product_varients')
                ->cascadeOnDelete();
        });
    }
};