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
        Schema::create('payrolls', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->noActionOnDelete();

    $table->unsignedTinyInteger('month');
    $table->unsignedSmallInteger('year');

    $table->enum('status', [
        'draft',
        'posted',
        'cancelled',
    ])->default('draft');

    $table->foreignId('journal_entry_id')
        ->nullable()
        ->constrained('journal_entries')
        ->noActionOnDelete();

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();

    $table->unique(['company_id', 'month', 'year']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
