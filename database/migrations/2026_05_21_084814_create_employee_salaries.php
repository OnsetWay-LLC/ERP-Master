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
      Schema::create('employee_salaries', function (Blueprint $table) {
    $table->id();

    $table->foreignId('employee_id')
        ->constrained('employees')
        ->noActionOnDelete();

    $table->enum('salary_mode', [
        'bank_transfer',
        'cash',
        'cheques',
    ]);

    $table->string('bank_account_name')->nullable();
    $table->string('bank_account_number')->nullable();
    $table->string('iban')->nullable();

    $table->decimal('salary_value', 15, 2);

    $table->decimal('social_security_deduction', 15, 2)->default(0);
    $table->decimal('insurance_deduction', 15, 2)->default(0);
    $table->decimal('tax_deduction', 15, 2)->default(0);

    $table->date('effective_from')->nullable();
    $table->date('effective_to')->nullable();

    $table->boolean('is_active')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
