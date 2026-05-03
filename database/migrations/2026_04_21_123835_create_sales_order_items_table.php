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

    $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
    $table->foreignId('item_id')->constrained()->noActionOnDelete();

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
        Schema::dropIfExists('sales_order_items');
    }
};
