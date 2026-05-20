<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('asset_category_id')
                ->constrained('asset_categories')
                ->noActionOnDelete();

            $table->string('item_code');
            $table->string('item_name');

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'item_code', 'deleted_at'], 'asset_items_company_code_unique');
            $table->index(['company_id', 'asset_category_id']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_items');
    }
};