<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {

            $table->foreignId('sales_order_item_id')
                ->nullable()
                ->after('sales_invoice_id')
                ->constrained('sales_order_items')
                ->noActionOnDelete();

            $table->foreignId('delivery_note_item_id')
                ->nullable()
                ->after('sales_order_item_id')
                ->constrained('delivery_note_items')
                ->noActionOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {

            $table->dropForeign(['sales_order_item_id']);
            $table->dropForeign(['delivery_note_item_id']);

            $table->dropColumn([
                'sales_order_item_id',
                'delivery_note_item_id',
            ]);
        });
    }
};