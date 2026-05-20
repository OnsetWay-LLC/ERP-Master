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
       Schema::create('general_ledger', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

    $table->foreignId('journal_entry_id')
        ->constrained('journal_entries')
        ->noActionOnDelete();

    $table->foreignId('journal_entry_line_id')
        ->constrained('journal_entry_lines')
        ->noActionOnDelete();

    $table->foreignId('account_id')
        ->constrained('chart_of_accounts')
        ->noActionOnDelete();

    $table->date('entry_date');

    $table->decimal('debit', 18, 2)->default(0);
    $table->decimal('credit', 18, 2)->default(0);

    $table->decimal('balance', 18, 2)->default(0);

    $table->text('description')->nullable();

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();

    $table->index(['company_id', 'account_id']);
    $table->index(['company_id', 'entry_date']);
    $table->index(['company_id', 'journal_entry_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_ledger');
    }
};
