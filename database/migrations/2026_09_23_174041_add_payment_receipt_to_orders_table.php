<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // payment_receipt already exists in the orders table.
    }

    public function down(): void
    {
        // Do nothing.
    }
};