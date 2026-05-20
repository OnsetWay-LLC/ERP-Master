<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

    $table->string('entry_number');
   
    $table->date('entry_date');
$table->decimal('total_debit', 18, 2)->default(0);
$table->decimal('total_credit', 18, 2)->default(0);
$table->timestamp('posted_at')->nullable();
$table->timestamp('cancelled_at')->nullable();
    $table->text('description')->nullable();
    $table->unsignedBigInteger('reversed_entry_id')->nullable();

    $table->enum('status', ['draft', 'posted','cancelled'])->default('draft');

    $table->foreignId('created_by')
        ->nullable()
        ->constrained('users')
        ->noActionOnDelete();

    $table->timestamps();

    $table->unique(['company_id', 'entry_number']);
    $table->index(['company_id', 'entry_date']);
    $table->index(['company_id', 'status']);
});
    }
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};