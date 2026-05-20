<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('name_ar');
            $table->string('name_en');

            $table->string('finance_book')->nullable();

            $table->enum('depreciation_method', [
                'straight_line',
                'double_declining_balance',
                'written_down_value',
                'manual',
            ]);

            $table->unsignedInteger('frequency_month')->nullable();
            $table->unsignedInteger('total_depreciation_count')->nullable();
            $table->unsignedTinyInteger('depreciation_posting_day')->nullable();

            $table->decimal('depreciation_rate', 8, 2)->nullable();

            $table->foreignId('fixed_asset_account_id')
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('accumulated_depreciation_account_id')
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->foreignId('depreciation_expense_account_id')
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();
            $table->foreignId('capital_work_in_progress_account_id')
    ->nullable()
     ->constrained('chart_of_accounts')
    ->noActionOnDelete();
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'name_ar', 'deleted_at'], 'asset_cat_company_name_ar_unique');
            $table->unique(['company_id', 'name_en', 'deleted_at'], 'asset_cat_company_name_en_unique');

            $table->index(['company_id', 'depreciation_method']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_categories');
    }
};