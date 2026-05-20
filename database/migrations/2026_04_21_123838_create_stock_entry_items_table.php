<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_entry_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_entry_id')
                ->constrained('stock_entries')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->noActionOnDelete();

            $table->string('barcode')->nullable();

            $table->foreignId('source_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->foreignId('target_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->decimal('quantity', 18, 2);

            $table->decimal('basic_rate', 18, 2)->default(0);

            $table->decimal('incoming_value', 18, 2)->default(0);
            $table->decimal('outgoing_value', 18, 2)->default(0);
            $table->decimal('value_difference', 18, 2)->default(0);

            $table->timestamps();

            $table->index(['item_id']);
            $table->index(['source_warehouse_id']);
            $table->index(['target_warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_entry_items');
    }
};