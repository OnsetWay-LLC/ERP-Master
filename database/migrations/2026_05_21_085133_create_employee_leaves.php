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
        Schema::create('employee_leaves', function (Blueprint $table) {
    $table->id();

    $table->foreignId('employee_id')
        ->constrained('employees')
        ->noActionOnDelete();

    $table->enum('leave_type', [
        'annual',
        'sick',
        'death',
        'death_first_degree',
        'death_second_degree',
        'paternity',
        'maternity',
    ]);

    $table->date('from_date');
    $table->date('to_date');

    $table->decimal('days_count', 8, 2);

    $table->text('description')->nullable();

    $table->enum('status', [
        'pending',
        'approved',
        'rejected',
    ])->default('pending');

    $table->boolean('deduct_from_salary')->default(false);
    $table->decimal('salary_deduction_amount', 15, 2)->default(0);

    $table->foreignId('approved_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamp('approved_at')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leaves');
    }
};
