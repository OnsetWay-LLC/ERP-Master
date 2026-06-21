<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipt_taxes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_receipt_id')
                ->constrained('purchase_receipts')
                ->cascadeOnDelete();

            $table->foreignId('tax_template_id')
                ->nullable()
                ->constrained('tax_templates')
                ->noActionOnDelete();

            $table->foreignId('tax_template_line_id')
                ->nullable()
                ->constrained('tax_template_lines')
                ->noActionOnDelete();

            $table->string('title')->nullable();
            $table->string('type');

            $table->foreignId('account_id')
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->decimal('tax_rate', 8, 2)->nullable();
            $table->decimal('amount', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['purchase_receipt_id']);
            $table->index(['account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_receipt_taxes');
    }
};