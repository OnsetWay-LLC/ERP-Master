<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('series');

            $table->foreignId('asset_item_id')
                ->constrained('asset_items')
                ->noActionOnDelete();

            $table->foreignId('asset_category_id')
                ->constrained('asset_categories')
                ->noActionOnDelete();

            $table->foreignId('location_id')
                ->constrained('asset_locations')
                ->noActionOnDelete();

            $table->string('asset_name_ar');
            $table->string('asset_name_en');

            $table->enum('asset_type', [
                'existing_asset',
                'composite_asset',
                'composite_component',
            ]);

            $table->date('purchase_date');

            $table->decimal('net_purchase_amount', 18, 2);

            $table->date('available_for_use_date');

            $table->unsignedInteger('asset_quantity')->default(1);

            $table->decimal('salvage_value', 18, 2)->default(0);

            $table->foreignId('purchase_receipt_id')
                ->nullable()
                ->constrained('purchase_receipts')
                ->noActionOnDelete();

            $table->enum('status', [
                'active',
                'disposed',
                'inactive',
            ])->default('active');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'series'], 'assets_company_series_unique');

            $table->index(['company_id', 'asset_item_id']);
            $table->index(['company_id', 'asset_category_id']);
            $table->index(['company_id', 'location_id']);
            $table->index(['company_id', 'asset_type']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};