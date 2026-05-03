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

    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('customer_id')->constrained()->noActionOnDelete();

    $table->string('order_number')->unique();
    $table->date('order_date');

    $table->decimal('total_amount', 18, 2)->default(0);

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
        Schema::dropIfExists('sales_orders');
    }
};
