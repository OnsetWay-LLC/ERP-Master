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
        Schema::create('sales_returns', function (Blueprint $table) {

    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->noActionOnDelete();

    $table->foreignId('sales_invoice_id')
        ->constrained('sales_invoices')
        ->noActionOnDelete();

    $table->foreignId('customer_id')
        ->constrained('customers')
        ->noActionOnDelete();

    $table->string('return_number');

    $table->date('posting_date');
    $table->time('posting_time');

    $table->date('payment_due_date')->nullable();

    $table->text('return_reason')->nullable();

    $table->string('posting_method')
        ->default('default');

    $table->foreignId('sales_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->foreignId('customer_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('net_total',15,2)->default(0);

    $table->decimal('tax_total',15,2)->default(0);

    $table->decimal('fees_total',15,2)->default(0);

    $table->string('discount_apply_on')
        ->default('grand_total');

    $table->decimal('discount_percentage',5,2)
        ->default(0);

    $table->decimal('discount_amount',15,2)
        ->default(0);

    $table->decimal('grand_total',15,2)
        ->default(0);

    $table->decimal('outstanding_amount',15,2)
        ->default(0);

    $table->foreignId('journal_entry_id')
        ->nullable()
        ->constrained('journal_entries')
        ->noActionOnDelete();

    $table->string('status')
        ->default('draft');

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->unique([
        'company_id',
        'return_number'
    ]);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
