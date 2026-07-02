<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->foreignId('asset_item_id')
                ->nullable()
                ->after('item_id')
                ->constrained('asset_items')
                ->nullOnDelete();
        });

        DB::statement("ALTER TABLE purchase_invoice_items ALTER COLUMN item_id BIGINT NULL");
        DB::statement("ALTER TABLE purchase_invoice_items ALTER COLUMN warehouse_id BIGINT NULL");
    }

    public function down(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['asset_item_id']);
            $table->dropColumn('asset_item_id');
        });

        DB::statement("ALTER TABLE purchase_invoice_items ALTER COLUMN item_id BIGINT NOT NULL");
        DB::statement("ALTER TABLE purchase_invoice_items ALTER COLUMN warehouse_id BIGINT NOT NULL");
    }
};