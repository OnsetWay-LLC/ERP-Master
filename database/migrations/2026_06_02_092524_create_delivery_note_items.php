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
        Schema::create('delivery_note_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('delivery_note_id')->constrained('delivery_notes')->cascadeOnDelete();
    $table->foreignId('sales_order_item_id')->constrained('sales_order_items')->noActionOnDelete();
    $table->foreignId('pick_list_item_id')->constrained('pick_list_items')->noActionOnDelete();

    $table->foreignId('item_id')->constrained('items')->noActionOnDelete();
    $table->foreignId('warehouse_id')->constrained('warehouses')->noActionOnDelete();

    $table->string('item_code')->nullable();
    $table->string('item_name_ar')->nullable();
    $table->string('item_name_en')->nullable();

    $table->decimal('quantity', 18, 2);
    $table->decimal('rate', 18, 2);
    $table->decimal('amount', 18, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
    }
};
