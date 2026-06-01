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
        Schema::create('sales_order_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('sales_order_id')
        ->constrained('sales_orders')
        ->cascadeOnDelete();

    $table->foreignId('item_id')
        ->constrained('items')
        ->noActionOnDelete();

    $table->foreignId('warehouse_id')
        ->constrained('warehouses')
        ->noActionOnDelete();

    // Snapshot
    $table->string('item_code')->nullable();
    $table->string('item_name_ar')->nullable();
    $table->string('item_name_en')->nullable();

    $table->decimal('available_stock', 18, 2)->default(0);

    $table->decimal('quantity', 18, 2);
    $table->decimal('rate', 18, 2);
    $table->decimal('amount', 18, 2);

    $table->timestamps();

    $table->index(['sales_order_id']);
    $table->index(['item_id']);
    $table->index(['warehouse_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
    }
};
