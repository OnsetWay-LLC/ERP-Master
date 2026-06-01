<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        
        // 4. جدول رسوم الفاتورة
        Schema::create('sales_invoice_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
            $table->foreignId('fees_template_id')->constrained('fees_templates');
            $table->string('title');
            $table->string('type'); // percentage, fixed_amount
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->decimal('fees_rate', 5, 2)->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_fees');
    }
};
