<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->noActionOnDelete();

            $table->decimal('sub_accountant_max_discount', 5, 2)->default(0);
            $table->decimal('department_manager_max_discount', 5, 2)->default(0);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();

            $table->unique('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_settings');
    }
};