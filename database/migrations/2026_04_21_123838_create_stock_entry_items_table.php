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
      Schema::create('stock_entry_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('stock_entry_id')->constrained()->cascadeOnDelete();
    $table->foreignId('item_id')->constrained()->noActionOnDelete();

    $table->foreignId('source_warehouse_id')->nullable()->constrained('warehouses')->noActionOnDelete();
    $table->foreignId('target_warehouse_id')->nullable()->constrained('warehouses')->noActionOnDelete();

    $table->decimal('quantity', 18, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_entry_items');
    }
};
