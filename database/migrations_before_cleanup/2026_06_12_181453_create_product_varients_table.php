<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_varients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('title', 255);

            $table->json('images');

            /*
             * DECIMAL is preferable to DOUBLE for money.
             */
            $table->decimal('price', 12, 2);

            /*
             * Percentage: 0-100.
             */
            $table->unsignedTinyInteger('discount')
                ->default(0);

            /*
             * Stock cannot be negative.
             */
            $table->unsignedInteger('qty')
                ->default(0);

            $table->timestamps();

            /*
             * A product should not have two variants
             * with exactly the same title.
             */
            $table->unique(
                ['product_id', 'title'],
                'product_variant_product_title_unique'
            );

            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_varients');
    }
};
