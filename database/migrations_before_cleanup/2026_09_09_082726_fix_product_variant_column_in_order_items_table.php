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
    Schema::table('order_items', function (Blueprint $table) {
        if (!Schema::hasColumn('order_items', 'product_varient_id') && Schema::hasColumn('order_items', 'variant_id')) {
            // If your table has variant_id, rename it or ensure compatibility
            $table->renameColumn('variant_id', 'product_varient_id');
        } elseif (!Schema::hasColumn('order_items', 'product_varient_id')) {
            $table->unsignedBigInteger('product_varient_id')->nullable()->after('product_id');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            //
        });
    }
};
