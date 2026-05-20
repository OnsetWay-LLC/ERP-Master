<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_stocks', function (Blueprint $table) {
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

            $table->decimal('quantity', 18, 2)->default(0);

            $table->decimal('average_rate', 18, 2)->default(0);

            $table->decimal('stock_value', 18, 2)->default(0);

            $table->timestamps();

            $table->unique(
                ['company_id', 'item_id', 'warehouse_id'],
                'warehouse_stocks_unique'
            );

            $table->index(['company_id', 'warehouse_id']);
            $table->index(['company_id', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};