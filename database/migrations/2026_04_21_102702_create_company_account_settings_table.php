<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_account_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->unique()
                ->constrained('companies')
                ->cascadeOnDelete();

            // Bank / Cash / AR / AP
            $table->foreignId('default_bank_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('default_cash_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('default_receivable_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('default_payable_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            // Income
            $table->foreignId('default_direct_income_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('default_indirect_income_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            // Cost of Goods Sold
            $table->foreignId('default_cogs_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            // Expenses
            $table->foreignId('default_direct_expense_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('default_indirect_expense_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            // Payment Discount
            $table->foreignId('default_payment_discount_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

         

           

            $table->foreignId('gain_loss_asset_disposal_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            // Inventory
            $table->foreignId('default_inventory_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('inventory_adjustment_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            // Equity
            $table->foreignId('default_equity_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            // Other
            $table->foreignId('other_account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->timestamps();

            $table->index('default_bank_account_id');
            $table->index('default_cash_account_id');
            $table->index('default_receivable_account_id');
            $table->index('default_payable_account_id');
            $table->index('default_inventory_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_account_settings');
    }
};