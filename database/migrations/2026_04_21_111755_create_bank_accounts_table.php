<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('bank_id')
                ->constrained('banks')
                ->noActionOnDelete();

            $table->string('account_name');
            $table->string('iban')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('swift_code_bic')->nullable();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};