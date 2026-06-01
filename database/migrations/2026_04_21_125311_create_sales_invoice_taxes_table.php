<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
         // 3. جدول ضرائب الفاتورة
        Schema::create('sales_invoice_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
            $table->foreignId('tax_template_id')->constrained('tax_templates');
            $table->foreignId('tax_template_line_id')->constrained('tax_template_lines');
            $table->string('title');
            $table->string('type'); // on_net_total, actual
            $table->foreignId('account_id')->constrained('chart_of_accounts');
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_taxes');
    }
};