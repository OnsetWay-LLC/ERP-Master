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
    'confirmed',
    'in_process',    // الحالة المضافة: قيد التجهيز
    'in_transit',    // الحالة المضافة: قيد التوصيل/النقل
    'delivered',
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
