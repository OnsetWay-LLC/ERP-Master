<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_type_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->noActionOnDelete();

            $table->enum('gender', [
                'male',
                'female',
                'any',
            ])->default('any');

            $table->enum('marital_status', [
                'single',
                'married',
                'any',
            ])->default('any');

            $table->decimal('days', 8, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['leave_type_id', 'gender', 'marital_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_type_rules');
    }
};