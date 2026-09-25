<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY payment_method ENUM('cod', 'esewa', 'bank')
            NOT NULL DEFAULT 'cod'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE orders
            MODIFY payment_method ENUM('cod', 'kalti')
            NOT NULL DEFAULT 'cod'
        ");
    }
};