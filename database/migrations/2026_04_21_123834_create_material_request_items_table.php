<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_request_items', function (Blueprint $table) {
    $table->id();

    $table->foreignId('material_request_id')
        ->constrained('material_requests')
        ->cascadeOnDelete();

    $table->foreignId('item_id')
        ->constrained('items')
        ->noActionOnDelete();

    $table->string('barcode')->nullable();

    $table->foreignId('warehouse_id')
        ->constrained('warehouses')
        ->noActionOnDelete();

    $table->date('required_by_date');

    $table->decimal('required_qty', 15, 2);

    $table->decimal('ordered_qty', 15, 2)->default(0);

    $table->decimal('received_qty', 15, 2)->default(0);

    $table->enum('status', [
        'pending',
        'ordered',
        'partially_received',
        'received',
        'cancelled',
    ])->default('pending');

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('material_request_items');
    }
};