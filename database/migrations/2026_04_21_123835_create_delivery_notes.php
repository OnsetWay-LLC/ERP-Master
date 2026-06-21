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
       Schema::create('delivery_notes', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained('companies')->noActionOnDelete();
    $table->foreignId('sales_order_id')->constrained('sales_orders')->noActionOnDelete();
    $table->foreignId('pick_list_id')->constrained('pick_lists')->noActionOnDelete();
    $table->foreignId('customer_id')->constrained('customers')->noActionOnDelete();

    $table->string('delivery_note_number');
    $table->date('posting_date');
    $table->time('posting_time');

    $table->decimal('total_qty', 18, 2)->default(0);
    $table->decimal('net_total', 18, 2)->default(0);
    $table->decimal('tax_total', 18, 2)->default(0);
    $table->decimal('fees_total', 18, 2)->default(0);
    $table->decimal('discount_percentage', 8, 2)->default(0);
    $table->decimal('discount_amount', 18, 2)->default(0);
    $table->decimal('grand_total', 18, 2)->default(0);

    $table->enum('status', [
        'draft',
        'to_bill',
        'completed',
        'cancelled',
    ])->default('draft');

    $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();

    $table->timestamps();

    $table->unique(['company_id', 'delivery_note_number']);
    $table->unique('pick_list_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};
