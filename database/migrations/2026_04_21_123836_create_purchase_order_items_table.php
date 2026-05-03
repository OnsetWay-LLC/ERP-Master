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

    $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('item_id')->constrained()->noActionOnDelete();
    $table->foreignId('material_request_item_id')
    ->nullable()
    ->constrained('material_request_items')
    ->noActionOnDelete();

$table->decimal('received_qty', 18, 2)->default(0);
    $table->foreignId('target_warehouse_id')
    ->constrained('warehouses')
    ->noActionOnDelete();

$table->string('item_code')->nullable();
$table->string('barcode')->nullable();

$table->date('required_by')->nullable();
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
        Schema::dropIfExists('purchase_order_items');
    }
};
