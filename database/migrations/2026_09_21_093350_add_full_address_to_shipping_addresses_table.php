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
        $table->string('full_address')->nullable()->after('id'); // or wherever appropriate
    });
}

public function down(): void
{
    Schema::table('shipping_addresses', function (Blueprint $table) {
        $table->dropColumn('full_address');
    });
}
};
