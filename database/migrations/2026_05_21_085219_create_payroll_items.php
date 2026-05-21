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
       Schema::create('payroll_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('payroll_id')
        ->constrained('payrolls')
        ->noActionOnDelete();

    $table->foreignId('employee_id')
        ->constrained('employees')
        ->noActionOnDelete();

    $table->decimal('basic_salary', 15, 2)->default(0);

    $table->decimal('total_allowances', 15, 2)->default(0);

    $table->decimal('social_security_deduction', 15, 2)->default(0);
    $table->decimal('insurance_deduction', 15, 2)->default(0);
    $table->decimal('tax_deduction', 15, 2)->default(0);
    $table->decimal('leave_deduction', 15, 2)->default(0);

    $table->decimal('total_deductions', 15, 2)->default(0);
    $table->decimal('net_salary', 15, 2)->default(0);

    $table->enum('salary_mode', [
        'bank_transfer',
        'cash',
        'cheques',
    ]);

    $table->timestamps();

    $table->unique(['payroll_id', 'employee_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
