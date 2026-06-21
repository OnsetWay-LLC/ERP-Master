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
        Schema::create('sales_payments', function (Blueprint $table) {

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

    $table->string('payment_number');

    $table->date('payment_date');
    $table->time('payment_time');

    $table->enum('posting_method', [
        'default',
        'manual'
    ])->default('default');

    $table->enum('payment_mode', [
        'cash',
        'bank'
    ]);

    $table->foreignId('receivable_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->foreignId('payment_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('invoice_amount',15,2);

    $table->decimal('paid_amount',15,2);

    $table->decimal('outstanding_before',15,2);

    $table->decimal('outstanding_after',15,2);

    $table->enum('status',[
        'draft',
        'submitted',
        'cancelled'
    ])->default('draft');

    $table->foreignId('journal_entry_id')
        ->nullable()
        ->constrained('journal_entries')
        ->noActionOnDelete();

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_payments');
    }
};
