<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. الجدول الرئيسي لفاتورة المبيعات
        Schema::create('sales_invoices', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->noActionOnDelete();

    $table->foreignId('customer_id')
        ->constrained('customers')
        ->noActionOnDelete();

    $table->foreignId('sales_order_id')
        ->nullable()
        ->constrained('sales_orders')
        ->noActionOnDelete();
 $table->foreignId('sales_person_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('sales_people')
                ->noActionOnDelete();
    $table->foreignId('delivery_note_id')
        ->nullable()
        ->constrained('delivery_notes')
        ->noActionOnDelete();

    $table->string('invoice_number');

    $table->date('posting_date');
    $table->time('posting_time');
    $table->date('payment_due_date')->nullable();

    $table->string('posting_method')->default('default');

    $table->foreignId('receivable_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->foreignId('sales_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->foreignId('cogs_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->foreignId('stock_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->string('payment_mode');

    $table->foreignId('payment_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('net_total', 15, 2)->default(0);
    $table->decimal('tax_total', 15, 2)->default(0);
    $table->decimal('fees_total', 15, 2)->default(0);
    $table->decimal('discount_percentage', 5, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);
    $table->decimal('grand_total', 15, 2)->default(0);

    $table->enum('status', ['draft', 'submitted', 'cancelled'])->default('draft');

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['company_id', 'invoice_number']);
    $table->index(['company_id', 'status']);
    $table->index(['sales_order_id']);
    $table->index(['delivery_note_id']);
});
    }
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};