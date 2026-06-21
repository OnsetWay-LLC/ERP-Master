<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

    $table->string('name_ar');
    $table->string('name_en');

    $table->boolean('is_group')->default(false);

    $table->foreignId('parent_id')
        ->nullable()
        ->constrained('warehouses')
        ->noActionOnDelete();

    $table->foreignId('sales_person_id')
        ->nullable()
        ->constrained('sales_people')
        ->noActionOnDelete();

    $table->decimal('opening_balance', 15, 2)->default(0);

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['company_id', 'name_ar']);
    $table->unique(['company_id', 'name_en']);
});
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};