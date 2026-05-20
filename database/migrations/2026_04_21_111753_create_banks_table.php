<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('banks', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

$table->string('name_ar');
$table->string('name_en');
    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

$table->unique(['company_id', 'name_ar', 'deleted_at'], 'banks_company_name_ar_deleted_unique');
$table->unique(['company_id', 'name_en', 'deleted_at'], 'banks_company_name_en_deleted_unique');});
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};