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
       Schema::create('purchase_return_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('purchase_return_id')
        ->constrained('purchase_returns')
        ->cascadeOnDelete();

    $table->foreignId('purchase_invoice_item_id')
        ->nullable()
        ->constrained('purchase_invoice_items');

    $table->foreignId('item_id')
        ->constrained('items');

    $table->string('barcode')->nullable();

    $table->decimal('original_quantity', 18, 2);

    // تخزن سالبة مثل -2
    $table->decimal('quantity', 18, 2);

    $table->decimal('rate', 18, 2);

    // تخزن سالبة quantity * rate
    $table->decimal('amount', 18, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
    }
};
