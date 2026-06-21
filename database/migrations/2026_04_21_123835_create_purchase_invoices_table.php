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
       Schema::create('purchase_invoices', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained('companies')->noActionOnDelete();
    $table->foreignId('purchase_receipt_id')->nullable()->constrained('purchase_receipts')->noActionOnDelete();
    $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->noActionOnDelete();
    $table->foreignId('supplier_id')->constrained('suppliers')->noActionOnDelete();

    $table->string('invoice_number');
    $table->date('posting_date');
    $table->time('posting_time');
    $table->date('due_date')->nullable();

    $table->string('supplier_invoice_no')->nullable();
    $table->date('supplier_invoice_date')->nullable();

    $table->enum('posting_method', ['default', 'manual'])->default('default');
    $table->enum('payment_mode', ['credit', 'cash', 'bank', 'cheque', 'credit_card'])->default('credit');

    $table->foreignId('stock_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
    $table->foreignId('purchase_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
    $table->foreignId('supplier_payable_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
    $table->foreignId('cash_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
    $table->foreignId('bank_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();

    $table->decimal('total_qty', 15, 2)->default(0);
    $table->decimal('net_total', 15, 2)->default(0);
    $table->decimal('tax_total', 15, 2)->default(0);
    $table->decimal('fees_total', 15, 2)->default(0);
    $table->decimal('discount_percentage', 5, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);
    $table->decimal('grand_total', 15, 2)->default(0);
    $table->decimal('paid_amount', 15, 2)->default(0);
    $table->decimal('outstanding_amount', 15, 2)->default(0);
    $table->decimal('returned_amount', 18, 2)->default(0);

$table->enum('return_status', [
    'not_returned',
    'partially_returned',
    'returned'
])->default('not_returned');

    $table->enum('status', ['draft', 'submitted', 'cancelled'])->default('draft');

    $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->noActionOnDelete();
    $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['company_id', 'invoice_number']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoices');
    }
};
