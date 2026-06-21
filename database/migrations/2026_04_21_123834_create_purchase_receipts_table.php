<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_id')
                ->constrained('purchase_orders')
                ->noActionOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->noActionOnDelete();

            $table->string('receipt_number');
            $table->date('receipt_date');
            $table->time('posting_time');

            $table->decimal('total_qty', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('fees_total', 15, 2)->default(0);
            $table->decimal('additional_discount_percentage', 5, 2)->default(0);
            $table->decimal('additional_discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            $table->enum('status', [
                'draft',
                'submitted',
                'cancelled'
            ])->default('draft');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'receipt_number']);
            $table->index(['company_id', 'purchase_order_id']);
            $table->index(['company_id', 'supplier_id']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipts');
    }
};