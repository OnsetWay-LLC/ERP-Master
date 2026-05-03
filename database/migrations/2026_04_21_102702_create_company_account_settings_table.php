<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //يخزن الحسابات الافتراضية للشركة (Core requirement)
        //*هذا أهم جدول بعد COA:كل العمليات لاحقًا (بيع/شراء/مخزون) تعتمد عليه ما بدنا نكرر اختيار الحسابات كل مرة
        Schema::create('company_account_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->unique()
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('default_bank_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_cash_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_receivable_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_payable_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_income_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_cogs_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_discount_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_accumulated_depreciation_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_depreciation_expense_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_asset_disposal_gain_loss_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_inventory_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
            $table->foreignId('default_sales_tax_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();
$table->foreignId('default_purchase_tax_account_id')->nullable()->constrained('chart_of_accounts')->noActionOnDelete();

            $table->timestamps();   
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_account_settings');
    }
};