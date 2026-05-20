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

$table->string('account_name_ar');
$table->string('account_name_en');
    $table->string('iban')->nullable();
    $table->string('branch_code')->nullable();
    $table->string('bank_account_no');
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

    $table->unique(
        ['company_id', 'bank_id', 'bank_account_no', 'deleted_at'],
        'bank_accounts_bank_no_unique'
    );

    $table->unique(
        ['company_id', 'iban', 'deleted_at'],
        'bank_accounts_iban_unique'
    );

    $table->index(['company_id', 'bank_id']);
    $table->index(['company_id', 'account_id']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};