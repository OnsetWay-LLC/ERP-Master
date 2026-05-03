<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //مهم: في النسخة النهائية من المتطلبات، Item Group مربوط بـ Warehouse
        Schema::create('item_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->string('name_ar');
            $table->string('name_en');

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
        Schema::dropIfExists('item_groups');
    }
};