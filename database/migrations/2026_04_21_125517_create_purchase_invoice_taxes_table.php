<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoice_taxes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_invoice_id')
                ->constrained('purchase_invoices')
                ->cascadeOnDelete();

            $table->foreignId('tax_template_id')
                ->nullable()
                ->constrained('tax_templates')
                ->noActionOnDelete();

            $table->string('type'); // actual, on_net_total

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->decimal('tax_rate', 8, 2)->nullable();
            $table->decimal('amount', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_taxes');
    }
};