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
        Schema::create('purchase_payments', function (Blueprint $table) {
    $table->id();

    $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();

    $table->decimal('amount', 18, 2);
    $table->date('payment_date');

    $table->string('payment_method'); // cash, bank

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
