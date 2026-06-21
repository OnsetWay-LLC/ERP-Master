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
        Schema::create('payment_entry_references', function (Blueprint $table) {
    $table->id();

    $table->foreignId('payment_entry_id')
        ->constrained('payment_entries')
        ->cascadeOnDelete();

    $table->foreignId('purchase_invoice_id')
        ->constrained('purchase_invoices');

    $table->decimal('invoice_amount', 18, 2);

    $table->decimal('outstanding_before_payment', 18, 2);

    $table->decimal('allocated_amount', 18, 2);

    $table->decimal('outstanding_after_payment', 18, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_entry_references');
    }
};
