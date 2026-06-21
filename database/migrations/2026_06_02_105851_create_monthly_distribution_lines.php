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
        Schema::create('monthly_distribution_lines', function (Blueprint $table) {
    $table->id();

    $table->foreignId('monthly_distribution_id')
        ->constrained('monthly_distributions')
        ->cascadeOnDelete();

    $table->unsignedTinyInteger('month'); // 1 إلى 12
    $table->decimal('percentage', 8, 2)->default(0);

    $table->timestamps();

    $table->unique(['monthly_distribution_id', 'month']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_distribution_lines');
    }
};
