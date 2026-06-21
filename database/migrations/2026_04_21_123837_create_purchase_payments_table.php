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
        Schema::create('payment_entries', function (Blueprint $table) {
    $table->id();

    $table->string('series')->unique();

    $table->date('posting_date');

    $table->foreignId('supplier_id')
        ->constrained('suppliers');

    $table->enum('payment_mode', [
        'cash',
        'bank'
    ]);

    $table->foreignId('paid_from_account_id')
        ->constrained('chart_of_accounts');

    $table->foreignId('payable_account_id')
        ->constrained('chart_of_accounts');

    $table->decimal('paid_amount', 18, 2);

    $table->string('reference_no')->nullable();

    $table->date('reference_date')->nullable();

    $table->text('remarks')->nullable();

    $table->enum('status', [
        'draft',
        'submitted',
        'cancelled'
    ])->default('draft');

    $table->foreignId('journal_entry_id')
        ->nullable()
        ->constrained('journal_entries');

    $table->foreignId('created_by')
        ->constrained('users');

    $table->timestamp('submitted_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();

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
