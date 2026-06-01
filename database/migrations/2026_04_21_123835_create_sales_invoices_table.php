<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. الجدول الرئيسي لفاتورة المبيعات
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('invoice_number')->unique();
            $table->date('posting_date');
            $table->time('posting_time');
            $table->date('payment_due_date')->nullable();
            
            // الحسابات المحاسبية للترحيل
            $table->string('posting_method')->default('default'); // default, manual
            $table->foreignId('receivable_account_id')->nullable()->constrained('chart_of_accounts');
            $table->foreignId('sales_account_id')->nullable()->constrained('chart_of_accounts');
            $table->foreignId('cogs_account_id')->nullable()->constrained('chart_of_accounts');
            $table->foreignId('stock_account_id')->nullable()->constrained('chart_of_accounts');
            
            // طرق الدفع والإجماليات
            $table->string('payment_mode'); // cash, bank, credit
            $table->foreignId('payment_account_id')->nullable()->constrained('chart_of_accounts'); // حساب الكاش أو البنك المختار
            
            $table->decimal('net_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('fees_total', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            
            $table->enum('status', ['draft', 'submitted', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        

       

    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};