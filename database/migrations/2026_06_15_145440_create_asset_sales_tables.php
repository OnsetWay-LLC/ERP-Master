<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('series');

            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('asset_category_id');

            $table->decimal('sell_qty', 15, 2);
            $table->decimal('asset_qty_before_sale', 15, 2);
            $table->decimal('asset_qty_after_sale', 15, 2)->default(0);

            $table->date('posting_date');
            $table->time('posting_time')->nullable();

            $table->unsignedBigInteger('warehouse_id')->nullable();

            $table->decimal('rate', 15, 2);
            $table->decimal('sale_value', 15, 2);

            $table->decimal('original_asset_cost', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);

            $table->decimal('sold_asset_cost', 15, 2);
            $table->decimal('sold_accumulated_depreciation', 15, 2);
            $table->decimal('book_value', 15, 2);

            $table->decimal('profit_amount', 15, 2)->default(0);
            $table->decimal('loss_amount', 15, 2)->default(0);

            $table->string('payment_mode')->default('cash'); // cash, bank, credit
            $table->unsignedBigInteger('receivable_account_id')->nullable();
            $table->unsignedBigInteger('cash_account_id')->nullable();
            $table->unsignedBigInteger('bank_account_id')->nullable();

            $table->unsignedBigInteger('fixed_asset_account_id');
            $table->unsignedBigInteger('accumulated_depreciation_account_id');
            $table->unsignedBigInteger('gain_account_id');
            $table->unsignedBigInteger('loss_account_id');

            $table->unsignedBigInteger('sales_invoice_id')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();

            $table->string('status')->default('draft'); // draft, submitted, cancelled
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'series']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_sales');
    }
};