<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('request_number');

            $table->date('request_date');
            $table->date('required_by_date')->nullable();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->noActionOnDelete();

            $table->enum('status', [
                'draft',
                'submitted',
                'ordered',
                'completed',
                'cancelled',
            ])->default('draft');

            $table->text('remarks')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'request_number']);
            $table->index(['company_id', 'warehouse_id']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};