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

    $table->string('type'); // actual, on_net_total

    $table->foreignId('account_id')
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('tax_rate', 8, 2)->nullable();
    $table->decimal('amount', 18, 2)->default(0);

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
