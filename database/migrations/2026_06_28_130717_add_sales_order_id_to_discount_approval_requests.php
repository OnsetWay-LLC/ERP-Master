<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_approval_requests', function (Blueprint $table) {
            $table->foreignId('sales_order_id')
                ->nullable()
                ->after('company_id')
                ->constrained('sales_orders')
                ->cascadeOnDelete();
        });

        DB::statement("
            ALTER TABLE discount_approval_requests
            ALTER COLUMN sales_invoice_id BIGINT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('discount_approval_requests', function (Blueprint $table) {
            $table->dropForeign(['sales_order_id']);
            $table->dropColumn('sales_order_id');
        });

        DB::statement("
            ALTER TABLE discount_approval_requests
            ALTER COLUMN sales_invoice_id BIGINT NOT NULL
        ");
    }
};