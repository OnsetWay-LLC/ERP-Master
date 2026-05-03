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
        Schema::create('sales_invoices', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('customer_id')->constrained()->noActionOnDelete();

    $table->string('invoice_number')->unique();
    $table->date('invoice_date');

    $table->decimal('net_total', 18, 2)->default(0);
    $table->decimal('tax_amount', 18, 2)->default(0);
    $table->decimal('discount_amount', 18, 2)->default(0);
    $table->decimal('grand_total', 18, 2)->default(0);

    $table->string('status')->default('draft');

    $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
