<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sales_person_targets', function (Blueprint $table) {
    $table->id();

    $table->foreignId('sales_person_id')
        ->constrained('sales_people')
        ->cascadeOnDelete();

    $table->foreignId('item_group_id')
        ->constrained('item_groups')
        ->noActionOnDelete();

    $table->foreignId('monthly_distribution_id')
        ->constrained('monthly_distributions')
        ->noActionOnDelete();

    $table->decimal('target_amount', 18, 2);
    
    $table->timestamps();

    $table->unique([
        'sales_person_id',
        'item_group_id',
        'monthly_distribution_id'
    ], 'sales_person_target_unique');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_person_targets');
    }
};
