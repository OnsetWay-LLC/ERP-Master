<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_template_lines', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tax_template_id')
                ->constrained('tax_templates')
                ->cascadeOnDelete();

            $table->string('type'); // actual, on_net_total

            $table->foreignId('account_id')
                ->constrained('chart_of_accounts')
                ->noActionOnDelete();

            $table->decimal('amount', 18, 2)->nullable();
            $table->decimal('tax_rate', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_template_lines');
    }
};