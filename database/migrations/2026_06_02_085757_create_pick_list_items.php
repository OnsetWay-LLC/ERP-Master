<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pick_list_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('pick_list_id')
        ->constrained('pick_lists')
        ->cascadeOnDelete();

    $table->foreignId('sales_order_item_id')
        ->constrained('sales_order_items')
        ->noActionOnDelete();

    $table->foreignId('item_id')
        ->constrained('items')
        ->noActionOnDelete();

    $table->foreignId('warehouse_id')
        ->constrained('warehouses')
        ->noActionOnDelete();

    $table->string('item_code')->nullable();
    $table->string('item_name_ar')->nullable();
    $table->string('item_name_en')->nullable();

    $table->decimal('required_quantity', 18, 2);
    $table->decimal('picked_quantity', 18, 2)->default(0);

    $table->timestamps();

    $table->index('pick_list_id');
    $table->index('sales_order_item_id');
    $table->index('item_id');
    $table->index('warehouse_id');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('pick_list_items');
    }
};