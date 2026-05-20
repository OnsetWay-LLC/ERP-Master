<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ledger', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('item_id')
                ->constrained('items')
                ->noActionOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->date('entry_date');

            $table->string('reference_type'); 
            // stock_entry, sales_invoice, purchase_invoice

            $table->unsignedBigInteger('reference_id');

            $table->decimal('quantity_in', 18, 2)->default(0);
            $table->decimal('quantity_out', 18, 2)->default(0);

            $table->decimal('balance_qty', 18, 2)->default(0);

            $table->decimal('basic_rate', 18, 2)->default(0);
            $table->decimal('stock_value', 18, 2)->default(0);
            $table->decimal('balance_value', 18, 2)->default(0);

            $table->timestamps();

            $table->index(['company_id', 'item_id', 'warehouse_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['company_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledger');
    }
};