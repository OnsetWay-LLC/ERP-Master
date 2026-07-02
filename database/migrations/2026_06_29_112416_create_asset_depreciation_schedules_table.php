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
    Schema::create('asset_depreciation_schedules', function (Blueprint $table) {
        $table->id();

        $table->foreignId('company_id')
            ->constrained('companies')
            ->noActionOnDelete();

        $table->foreignId('asset_id')
            ->constrained('assets')
              ->noActionOnDelete();

        $table->integer('schedule_no');

        $table->date('schedule_date');

        $table->decimal('depreciation_amount', 15, 3);

        $table->enum('status', [
            'pending',
            'posted',
            'cancelled',
        ])->default('pending');

        $table->foreignId('journal_entry_id')
            ->nullable()
            ->constrained('journal_entries')
            ->nullOnDelete();

        $table->timestamps();

        $table->unique(['asset_id', 'schedule_no']);
    });
}

public function down(): void
{
    Schema::dropIfExists('asset_depreciation_schedules');
}
   
};
