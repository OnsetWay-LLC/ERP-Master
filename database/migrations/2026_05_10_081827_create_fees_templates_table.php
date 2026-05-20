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
        Schema::create('fees_templates', function (Blueprint $table) {

    $table->id();

    $table->foreignId('company_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->string('title');

    $table->enum('type', [
        'percentage',
        'fixed_amount'
    ]);

    $table->foreignId('account_id')
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->decimal('fees_rate', 10, 2)
        ->nullable();

    $table->decimal('amount', 10, 2)
        ->nullable();

    $table->boolean('is_active')
        ->default(true);

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
