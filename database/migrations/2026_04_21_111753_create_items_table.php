<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('item_group_id')
                ->constrained('item_groups')
                ->noActionOnDelete();

            $table->string('item_code');
            $table->string('name_ar');
            $table->string('name_en');

            $table->string('barcode')->nullable();

            $table->decimal('selling_price', 18, 2)->default(0);
            $table->decimal('purchase_price', 18, 2)->default(0);

            $table->string('currency_code', 10);
            $table->string('status')->default('active'); // active, inactive

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'item_code']);
            $table->unique(['company_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};