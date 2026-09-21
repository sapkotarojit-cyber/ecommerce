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
    Schema::table('shipping_addresses', function (Blueprint $table) {
        if (!Schema::hasColumn('shipping_addresses', 'name')) {
            $table->string('name')->after('user_id');
        }
        if (!Schema::hasColumn('shipping_addresses', 'phone')) {
            $table->string('phone')->after('name');
        }
        if (!Schema::hasColumn('shipping_addresses', 'region')) {
            $table->string('region')->after('phone');
        }
        if (!Schema::hasColumn('shipping_addresses', 'address')) {
            $table->text('address')->after('region');
        }
        if (!Schema::hasColumn('shipping_addresses', 'landmark')) {
            $table->string('landmark')->nullable()->after('address');
        }
        if (!Schema::hasColumn('shipping_addresses', 'address_type')) {
            $table->string('address_type')->default('Home')->after('landmark');
        }
        if (!Schema::hasColumn('shipping_addresses', 'is_default_shipping')) {
            $table->boolean('is_default_shipping')->default(false)->after('address_type');
        }
        if (!Schema::hasColumn('shipping_addresses', 'is_default_billing')) {
            $table->boolean('is_default_billing')->default(false)->after('is_default_shipping');
        }
    });
}

public function down(): void
{
    Schema::table('shipping_addresses', function (Blueprint $table) {
        $table->dropColumn([
            'name', 'phone', 'region', 'address', 
            'landmark', 'address_type', 
            'is_default_shipping', 'is_default_billing'
        ]);
    });
}
};
