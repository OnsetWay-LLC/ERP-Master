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
        Schema::create('purchase_return_taxes', function (Blueprint $table) {
    $table->id();

    $table->foreignId('purchase_return_id')
        ->constrained('purchase_returns')
        ->cascadeOnDelete();

    $table->foreignId('tax_template_line_id')
        ->nullable()
        ->constrained('tax_template_lines');

    $table->string('type');

    $table->foreignId('account_head_id')
        ->constrained('chart_of_accounts');

    $table->decimal('tax_rate', 8, 2)->default(0);

    // سالبة
    $table->decimal('amount', 18, 2)->default(0);

    // سالبة
    $table->decimal('total', 18, 2)->default(0);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_taxes');
    }
};
