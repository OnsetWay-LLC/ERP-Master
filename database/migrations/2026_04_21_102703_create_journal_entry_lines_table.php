<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();

           $table->foreignId('company_id')->constrained('companies')->noActionOnDelete();
$table->foreignId('journal_entry_id')->constrained('journal_entries')->noActionOnDelete();
$table->foreignId('account_id')->constrained('chart_of_accounts')->noActionOnDelete();

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'account_id']);
            $table->index(['company_id', 'journal_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};