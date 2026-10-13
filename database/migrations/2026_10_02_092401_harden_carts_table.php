<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First make sure existing data doesn't contain duplicates.
        $duplicates = DB::table('carts')
            ->select('user_id', 'varient_id', DB::raw('COUNT(*) as total'))
            ->whereNotNull('varient_id')
            ->groupBy('user_id', 'varient_id')
            ->having('total', '>', 1)
            ->exists();

        if ($duplicates) {
            throw new RuntimeException(
                'Cannot add the cart unique constraint because duplicate user/variant rows exist.'
            );
        }

        Schema::table('carts', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'varient_id'],
                'carts_user_variant_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_user_variant_unique');
        });
    }
};