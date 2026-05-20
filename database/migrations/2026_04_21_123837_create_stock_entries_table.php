<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('series');

            $table->enum('entry_type', [
                'material_receipt',
                'material_issue',
                'material_transfer',
            ]);

            $table->date('posting_date');
            $table->time('posting_time');

            $table->decimal('total_incoming_value', 18, 2)->default(0);
            $table->decimal('total_outgoing_value', 18, 2)->default(0);
            $table->decimal('value_difference', 18, 2)->default(0);

            $table->enum('status', [
                'draft',
                'submitted',
                'cancelled',
            ])->default('submitted');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'series'], 'stock_entries_company_series_unique');

            $table->index(['company_id', 'entry_type']);
            $table->index(['company_id', 'posting_date']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_entries');
    }
};