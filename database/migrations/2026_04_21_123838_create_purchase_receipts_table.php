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
                ->nullable()
                ->constrained('purchase_orders')
                ->noActionOnDelete();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->noActionOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->string('receipt_number');
            $table->date('receipt_date');

            $table->string('status')->default('draft'); 
            // draft, submitted, cancelled

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'receipt_number']);
            $table->index(['company_id', 'purchase_order_id']);
            $table->index(['company_id', 'supplier_id']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipts');
    }
};