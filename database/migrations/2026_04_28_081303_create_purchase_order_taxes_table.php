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
       Schema::create('purchase_order_taxes', function (Blueprint $table) {
    $table->id();

    $table->foreignId('purchase_order_id')
        ->constrained('purchase_orders')
        ->cascadeOnDelete();

    $table->foreignId('tax_template_id')
        ->nullable()
        ->constrained('tax_templates')
        ->noActionOnDelete();

    $table->foreignId('tax_template_line_id')
        ->nullable()
        ->constrained('tax_template_lines')
        ->noActionOnDelete();

    $table->string('title');
    $table->string('type');

    $table->foreignId('account_id')
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('tax_rate', 15, 2)->nullable();
    $table->decimal('amount', 15, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_taxes');
    }
};
