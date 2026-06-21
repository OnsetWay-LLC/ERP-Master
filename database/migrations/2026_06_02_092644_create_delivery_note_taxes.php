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
        Schema::create('delivery_note_taxes', function (Blueprint $table) {
    $table->id();

    $table->foreignId('delivery_note_id')->constrained('delivery_notes')->cascadeOnDelete();
    $table->foreignId('tax_template_id')->constrained('tax_templates')->noActionOnDelete();
    $table->foreignId('tax_template_line_id')->constrained('tax_template_lines')->noActionOnDelete();

    $table->string('title');
    $table->string('type');
    $table->foreignId('account_id')->constrained('chart_of_accounts')->noActionOnDelete();
    $table->decimal('tax_rate', 8, 2)->nullable();
    $table->decimal('amount', 18, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_taxes');
    }
};
