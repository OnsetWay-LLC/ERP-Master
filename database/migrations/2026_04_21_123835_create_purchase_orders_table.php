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
       Schema::create('purchase_orders', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('supplier_id')->constrained()->noActionOnDelete();
    $table->foreignId('material_request_id')
    ->nullable()
    ->constrained('material_requests')
    ->noActionOnDelete();
    $table->foreignId('tax_template_id')
    ->nullable()
    ->constrained('tax_templates')
    ->noActionOnDelete();

   $table->date('required_by')->nullable();

   $table->decimal('net_total', 18, 2)->default(0);
   $table->decimal('tax_total', 18, 2)->default(0);

   $table->decimal('additional_discount_percentage', 8, 2)->default(0);
   $table->decimal('additional_discount_amount', 18, 2)->default(0);

   $table->decimal('grand_total', 18, 2)->default(0);

    $table->softDeletes();
    $table->string('order_number')->unique();
    $table->date('order_date');

    $table->decimal('total_amount', 18, 2)->default(0);

    $table->string('status')->default('draft'); // draft, submitted, confirmed, completed, cancelled

    $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
