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

    $table->foreignId('company_id')->constrained('companies')->noActionOnDelete();
    $table->foreignId('supplier_id')->constrained('suppliers')->noActionOnDelete();

    $table->string('series');
    $table->date('posting_date');
    $table->date('required_by_date')->nullable();

    $table->decimal('total_quantity', 15, 2)->default(0);
    $table->decimal('net_total', 15, 2)->default(0);
    $table->decimal('tax_total', 15, 2)->default(0);
    $table->decimal('fees_total', 15, 2)->default(0);
    $table->decimal('discount_percentage', 5, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);
    $table->decimal('grand_total', 15, 2)->default(0);

    $table->enum('status', [
    'draft',
    'confirmed',
    'completed',
    'cancelled'
])->default('draft');

    $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['company_id', 'series']);
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
