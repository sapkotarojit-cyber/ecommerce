<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokans', function (Blueprint $table) {
            if (!Schema::hasColumn('dokans', 'name'))
                $table->string('name')->nullable()->after('company_name');

            if (!Schema::hasColumn('dokans', 'business_location'))
                $table->string('business_location')->nullable()->after('contact_number');

            if (!Schema::hasColumn('dokans', 'business_address'))
                $table->text('business_address')->nullable()->after('business_location');

            if (!Schema::hasColumn('dokans', 'business_reg_no'))
                $table->string('business_reg_no')->nullable()->after('business_address');

            if (!Schema::hasColumn('dokans', 'pan_no'))
                $table->string('pan_no')->nullable()->after('business_reg_no');

            if (!Schema::hasColumn('dokans', 'business_document'))
                $table->string('business_document')->nullable()->after('pan_no');

            if (!Schema::hasColumn('dokans', 'bank_name'))
                $table->string('bank_name')->nullable()->after('business_document');

            if (!Schema::hasColumn('dokans', 'bank_account_name'))
                $table->string('bank_account_name')->nullable()->after('bank_name');

            if (!Schema::hasColumn('dokans', 'bank_account_number'))
                $table->string('bank_account_number')->nullable()->after('bank_account_name');

            if (!Schema::hasColumn('dokans', 'bank_branch'))
                $table->string('bank_branch')->nullable()->after('bank_account_number');

            if (!Schema::hasColumn('dokans', 'bank_document'))
                $table->string('bank_document')->nullable()->after('bank_branch');

            if (!Schema::hasColumn('dokans', 'rejection_comment'))
                $table->text('rejection_comment')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        //
    }
};