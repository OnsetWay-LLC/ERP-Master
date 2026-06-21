<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_value_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('series');
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('asset_category_id');
            $table->date('posting_date');
            $table->string('finance_book')->nullable();

            $table->decimal('current_asset_value', 15, 2);
            $table->decimal('new_asset_value', 15, 2);
            $table->decimal('difference_amount', 15, 2);

            $table->unsignedBigInteger('difference_account_id');
            $table->unsignedBigInteger('journal_entry_id')->nullable();

            $table->string('status')->default('draft'); // draft, submitted
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'series']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_value_adjustments');
    }
};