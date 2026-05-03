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
       Schema::create('stock_ledger', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained()->cascadeOnDelete();

    $table->foreignId('item_id')->constrained()->noActionOnDelete();
    $table->foreignId('warehouse_id')->constrained('warehouses')->noActionOnDelete();

    $table->date('entry_date');

    $table->string('reference_type'); 
    // sales_invoice, purchase_invoice, stock_entry

    $table->unsignedBigInteger('reference_id');

    $table->decimal('quantity_in', 18, 2)->default(0);
    $table->decimal('quantity_out', 18, 2)->default(0);

    $table->decimal('balance_qty', 18, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};
