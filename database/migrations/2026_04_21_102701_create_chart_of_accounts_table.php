<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //يدعم: root + sub + type + tree + uniqueness per company
        
       Schema::create('chart_of_accounts', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

    $table->foreignId('parent_id')
        ->nullable()
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->string('name_ar');
    $table->string('name_en');

    $table->string('account_number');

    $table->string('root_category'); 
    $table->string('sub_category')->nullable();
    $table->string('account_type');
    $table->enum('account_level', ['parent', 'child'])->default('child');
    $table->boolean('is_active')->default(true);
    $table->boolean('is_system')->default(false);

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->unique(['company_id', 'account_number', 'deleted_at'], 'coa_company_number_deleted_unique');
    $table->unique(['company_id', 'name_ar', 'deleted_at'], 'coa_company_name_ar_deleted_unique');
    $table->unique(['company_id', 'name_en', 'deleted_at'], 'coa_company_name_en_deleted_unique');

    $table->index(['company_id', 'root_category']);
    $table->index(['company_id', 'sub_category']);
    $table->index(['company_id', 'account_type']);
    $table->index(['company_id', 'parent_id']);
    $table->index(['company_id', 'account_level']);
}); 
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};