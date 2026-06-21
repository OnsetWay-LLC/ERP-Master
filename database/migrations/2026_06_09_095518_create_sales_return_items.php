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
        Schema::create('sales_return_items', function (Blueprint $table) {

    $table->id();

    $table->foreignId('sales_return_id')
        ->constrained('sales_returns')
        ->cascadeOnDelete();

    $table->foreignId('sales_invoice_item_id')
        ->nullable()
        ->constrained('sales_invoice_items')
        ->noActionOnDelete();

    $table->foreignId('item_id')
        ->constrained('items')
        ->noActionOnDelete();

    $table->foreignId('warehouse_id')
        ->constrained('warehouses')
        ->noActionOnDelete();

    $table->string('item_code');

    $table->string('item_name_ar');

    $table->string('item_name_en');

    $table->decimal('original_qty',15,2);

    $table->decimal('returned_qty',15,2);

    $table->decimal('rate',15,2);

    $table->decimal('amount',15,2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};
