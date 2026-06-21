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
        Schema::create('purchase_invoice_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
    $table->foreignId('purchase_receipt_item_id')->nullable()->constrained('purchase_receipt_items')->noActionOnDelete();
    $table->foreignId('item_id')->constrained('items')->noActionOnDelete();
    $table->foreignId('warehouse_id')->constrained('warehouses')->noActionOnDelete();

    $table->string('item_code');
    $table->string('item_name_ar');
    $table->string('item_name_en');

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
        Schema::dropIfExists('purchase_invoice_items');
    }
};
