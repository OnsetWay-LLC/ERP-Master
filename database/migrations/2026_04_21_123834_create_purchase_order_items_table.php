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
        Schema::create('purchase_order_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('purchase_order_id')
        ->constrained('purchase_orders')
        ->cascadeOnDelete();

    $table->foreignId('material_request_id')
        ->nullable()
        ->constrained('material_requests')
        ->noActionOnDelete();

    $table->foreignId('item_id')->constrained('items')->noActionOnDelete();
    $table->foreignId('target_warehouse_id')->constrained('warehouses')->noActionOnDelete();

    $table->string('item_code');
    $table->string('item_name_ar');
    $table->string('item_name_en');

    $table->date('required_by_date')->nullable();

    $table->decimal('quantity', 15, 2);
    $table->decimal('rate', 15, 2);
    $table->decimal('amount', 15, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
