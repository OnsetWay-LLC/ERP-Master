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
       Schema::create('sales_return_fees', function (Blueprint $table) {

    $table->id();

    $table->foreignId('sales_return_id')
        ->constrained('sales_returns')
        ->cascadeOnDelete();

    $table->foreignId('fees_template_id')
        ->nullable()
        ->constrained('fees_templates')
        ->nullOnDelete();

    $table->string('title');

    $table->string('type');

    $table->foreignId('account_id')
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('fees_rate',15,2)
        ->nullable();

    $table->decimal('amount',15,2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_return_fees');
    }
};
