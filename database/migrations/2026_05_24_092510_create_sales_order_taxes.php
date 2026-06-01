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
     Schema::create('sales_order_taxes', function (Blueprint $table) {
    $table->id();

    $table->foreignId('sales_order_id')
        ->constrained('sales_orders')
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

    $table->enum('type', [
        'on_net_total',
        'actual',
    ]);

    $table->foreignId('account_id')
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('tax_rate', 8, 2)->nullable();
    $table->decimal('amount', 18, 2)->default(0);

    $table->timestamps();

    $table->index(['sales_order_id']);
    $table->index(['tax_template_id']);
    $table->index(['account_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_taxes');
    }
};
