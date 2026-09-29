<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // payment_method is already created correctly
        // in the orders table migration.
    }

    public function down(): void
    {
        // Nothing to reverse.
    }
};