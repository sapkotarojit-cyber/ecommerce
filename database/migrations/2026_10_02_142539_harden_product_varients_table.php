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
        Schema::table('product_varients', function (Blueprint $table) {
            /*
             * A product must not have two variants with the
             * same title.
             */
            $table->unique(
                ['product_id', 'title'],
                'product_varients_product_title_unique'
            );

            /*
             * Money must use fixed precision.
             */
            $table->decimal('price', 12, 2)->change();

            /*
             * Discount percentage:
             * application validation must enforce 0-100.
             */
            $table->unsignedInteger('discount')
                ->default(0)
                ->change();

            /*
             * Quantity cannot be negative.
             */
            $table->unsignedInteger('qty')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_varients', function (Blueprint $table) {
            $table->dropUnique(
                'product_varients_product_title_unique'
            );

            $table->double('price')->change();

            $table->integer('discount')
                ->default(0)
                ->change();

            $table->integer('qty')->change();
        });
    }
};
