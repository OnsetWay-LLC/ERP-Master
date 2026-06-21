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

    $table->string('request_number')->unique();

    $table->date('request_date');

    $table->date('required_by_date')->nullable();

    $table->enum('status', [
        'draft',
        'sent_to_purchase_order',
        'completed',
        'cancelled',
    ])->default('sent_to_purchase_order');

    $table->timestamp('sent_to_purchase_order_at')->nullable();

    $table->text('remarks')->nullable();

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};