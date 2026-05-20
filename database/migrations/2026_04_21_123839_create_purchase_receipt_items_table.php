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
                ->nullable()
                ->constrained('purchase_order_items')
                ->noActionOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->noActionOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->decimal('quantity', 18, 2);

            $table->timestamps();

            $table->index(['purchase_receipt_id', 'item_id']);
            $table->index(['purchase_order_item_id']);
            $table->index(['warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_items');
    }
};