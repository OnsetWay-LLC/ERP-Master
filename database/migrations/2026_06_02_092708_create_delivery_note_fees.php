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
        Schema::create('delivery_note_fees', function (Blueprint $table) {
    $table->id();

    $table->foreignId('delivery_note_id')->constrained('delivery_notes')->cascadeOnDelete();
    $table->foreignId('fees_template_id')->constrained('fees_templates')->noActionOnDelete();

    $table->string('title');
    $table->string('type');
    $table->foreignId('account_id')->constrained('chart_of_accounts')->noActionOnDelete();
    $table->decimal('fees_rate', 8, 2)->nullable();
    $table->decimal('amount', 18, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_fees');
    }
};
