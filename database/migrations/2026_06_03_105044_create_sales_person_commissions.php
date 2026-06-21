<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_person_commissions', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained('companies')->noActionOnDelete();
    $table->foreignId('sales_person_id')->constrained('sales_people')->noActionOnDelete();
    $table->foreignId('sales_person_target_id')->constrained('sales_person_targets')->noActionOnDelete();

    $table->date('from_date');
    $table->date('to_date');

    $table->decimal('target_amount', 18, 2);
    $table->decimal('total_period_target', 18, 2);
    $table->decimal('total_actual_sales', 18, 2);
    $table->decimal('achievement_percentage', 8, 2)->default(0);

    $table->decimal('commission_rate', 8, 2);
    $table->decimal('commission_amount', 18, 2)->default(0);

    $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->noActionOnDelete();
    $table->foreignId('employee_allowance_id')->nullable()->constrained('employee_allowances')->noActionOnDelete();

    $table->enum('status', ['calculated', 'posted', 'cancelled'])->default('calculated');

    $table->timestamp('posted_at')->nullable();

    $table->foreignId('posted_by')->nullable()->constrained('users')->noActionOnDelete();

    $table->timestamps();

    $table->unique(
        ['sales_person_id', 'sales_person_target_id', 'from_date', 'to_date'],
        'sales_person_commission_unique'
    );

    $table->index(['company_id', 'status']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_person_commissions');
    }
};