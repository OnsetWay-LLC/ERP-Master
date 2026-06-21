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
       Schema::create('sales_people', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained('companies')->noActionOnDelete();
    $table->foreignId('employee_id')->constrained('employees')->noActionOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users')->noActionOnDelete();

    $table->string('sales_person_name_ar');
    $table->string('sales_person_name_en');

    $table->decimal('commission_rate', 8, 2)->default(0);

    $table->boolean('use_default_account')->default(true);

    $table->foreignId('commission_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->foreignId('payroll_account_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->boolean('is_active')->default(true);

    $table->foreignId('created_by')->nullable()->constrained('users')->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['company_id', 'employee_id']);
    $table->index(['company_id', 'is_active']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_people');
    }
};
