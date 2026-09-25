<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokans', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id');
            $table->string('name')->after('company_name');

            $table->string('business_location')->after('contact_number');
            $table->text('business_address')->after('business_location');

            $table->string('business_reg_no')->after('business_address');
            $table->string('pan_no')->after('business_reg_no');
            $table->string('business_document')->after('pan_no');

            $table->string('bank_name')->after('business_document');
            $table->string('bank_account_name')->after('bank_name');
            $table->string('bank_account_number')->after('bank_account_name');
            $table->string('bank_branch')->after('bank_account_number');
            $table->string('bank_document')->after('bank_branch');

            $table->text('rejection_comment')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('dokans', function (Blueprint $table) {
            $table->dropColumn([
                'user_id',
                'name',
                'business_location',
                'business_address',
                'business_reg_no',
                'pan_no',
                'business_document',
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_branch',
                'bank_document',
                'rejection_comment',
            ]);
        });
    }
};