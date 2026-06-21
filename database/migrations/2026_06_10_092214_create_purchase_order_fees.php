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
        Schema::create('purchase_order_fees', function (Blueprint $table) {
    $table->id();

    $table->foreignId('purchase_order_id')
        ->constrained('purchase_orders')
        ->cascadeOnDelete();

    $table->foreignId('fees_template_id')
        ->nullable()
        ->constrained('fees_templates')
        ->noActionOnDelete();

    $table->string('title');
    $table->string('type'); // percentage / fixed_amount

    $table->foreignId('account_id')
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('fees_rate', 15, 2)->nullable();
    $table->decimal('amount', 15, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_fees');
    }
};
