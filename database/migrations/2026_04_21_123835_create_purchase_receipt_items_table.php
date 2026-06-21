<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_receipt_id')
                ->constrained('purchase_receipts')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_item_id')
                ->constrained('purchase_order_items')
                ->noActionOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->noActionOnDelete();

            $table->string('barcode')->nullable();

            $table->foreignId('accepted_warehouse_id')
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->foreignId('rejected_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->decimal('accepted_qty', 15, 2)->default(0);
            $table->decimal('rejected_qty', 15, 2)->default(0);
            $table->decimal('total_qty', 15, 2)->default(0);

            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['purchase_receipt_id', 'item_id']);
            $table->index(['purchase_order_item_id']);
            $table->index(['accepted_warehouse_id']);
            $table->index(['rejected_warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
    }
};