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
        Schema::create('payroll_tax_brackets', function (Blueprint $table) {
    $table->id();

    $table->foreignId('payroll_tax_setting_id')
        ->constrained('payroll_tax_settings')
        ->noActionOnDelete();

    $table->decimal('from_amount', 15, 2)->default(0);
    $table->decimal('to_amount', 15, 2)->nullable();

    $table->decimal('rate', 8, 2);

    $table->unsignedInteger('sort_order')->default(1);

    $table->timestamps();

    $table->index(['payroll_tax_setting_id', 'sort_order']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_tax_brackets');
    }
};
