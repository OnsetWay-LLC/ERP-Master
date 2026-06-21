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
    {Schema::create('purchase_returns', function (Blueprint $table) {
    $table->id();

    $table->string('series')->unique();

    $table->foreignId('purchase_invoice_id')
        ->constrained('purchase_invoices');

    $table->foreignId('supplier_id')
        ->constrained('suppliers');

    $table->date('posting_date');
    $table->time('posting_time')->nullable();
    $table->date('payment_due_date')->nullable();

    $table->foreignId('rejected_warehouse_id')
        ->nullable()
        ->constrained('warehouses');

    $table->foreignId('target_warehouse_id')
        ->nullable()
        ->constrained('warehouses');

    $table->boolean('use_default_account')->default(true);

    $table->foreignId('purchase_account_id')
        ->nullable()
        ->constrained('chart_of_accounts');

    $table->foreignId('supplier_account_id')
        ->nullable()
        ->constrained('chart_of_accounts');

    $table->foreignId('tax_template_id')
        ->nullable()
        ->constrained('tax_templates');

    $table->foreignId('fees_template_id')
        ->nullable()
        ->constrained('fees_templates');

    $table->enum('apply_additional_discount_on', [
        'grand_total',
        'net_total'
    ])->nullable();

    $table->decimal('additional_discount_percentage', 8, 2)->default(0);
    $table->decimal('additional_discount_amount', 18, 2)->default(0);

    $table->decimal('total_quantity', 18, 2)->default(0);
    $table->decimal('total_amount', 18, 2)->default(0);

    $table->decimal('net_total', 18, 2)->default(0);
    $table->decimal('tax_total', 18, 2)->default(0);
    $table->decimal('fees_total', 18, 2)->default(0);
    $table->decimal('grand_total', 18, 2)->default(0);

    $table->decimal('outstanding_amount', 18, 2)->default(0);

    $table->enum('status', [
        'draft',
        'submitted',
        'cancelled'
    ])->default('draft');

    $table->foreignId('journal_entry_id')
        ->nullable()
        ->constrained('journal_entries');

    $table->foreignId('created_by')
        ->constrained('users');

    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
