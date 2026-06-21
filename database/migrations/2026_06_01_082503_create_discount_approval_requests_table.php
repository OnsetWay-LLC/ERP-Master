<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_approval_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->noActionOnDelete();

            $table->foreignId('sales_invoice_id')
                ->constrained('sales_invoices')
                ->cascadeOnDelete();

            $table->foreignId('requested_by')
                ->constrained('users')
                ->noActionOnDelete();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->decimal('requested_discount_percentage', 5, 2);
            $table->decimal('allowed_discount_percentage', 5, 2);

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            $table->text('rejection_reason')->nullable();

            $table->timestamp('responded_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_approval_requests');
    }
};