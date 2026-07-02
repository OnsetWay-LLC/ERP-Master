<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->enum('invoice_type', [
                'from_receipt',
                'manual_inventory',
                'manual_asset',
            ])->default('from_receipt')->after('purchase_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_type');
        });
    }
};
