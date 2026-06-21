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
       Schema::create('sales_orders', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->noActionOnDelete();

    $table->foreignId('customer_id')
        ->constrained('customers')
        ->noActionOnDelete();
 $table->foreignId('sales_person_id')
                ->nullable()
                ->after('customer_id')
                ->constrained('sales_people')
                ->noActionOnDelete();
    $table->string('order_number');
    $table->date('order_date');
    $table->date('delivery_date')->nullable();

    $table->decimal('net_total', 18, 2)->default(0);
    $table->decimal('tax_total', 18, 2)->default(0);
    $table->decimal('fees_total', 18, 2)->default(0);

    $table->decimal('discount_percentage', 8, 2)->default(0);
    $table->decimal('discount_amount', 18, 2)->default(0);

    $table->decimal('grand_total', 18, 2)->default(0);

  $table->enum('status', [
    'draft',
    'delivery_and_to_bill',
    'to_bill',
    'completed',
    'cancelled',
])->default('draft');

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['company_id', 'order_number']);
    $table->index(['company_id', 'status']);
    $table->index(['company_id', 'order_date']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
