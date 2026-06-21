<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_repairs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('series');
            $table->unsignedBigInteger('asset_id');

            $table->string('repair_status')->default('pending'); // pending, completed, cancelled
            $table->date('failure_date');
            $table->timestamp('completed_date')->nullable();

            $table->text('error_description')->nullable();
            $table->text('actions_performed')->nullable();

            $table->decimal('repair_cost_total', 15, 2)->default(0);

            $table->string('status')->default('draft'); // draft, submitted
            $table->unsignedBigInteger('journal_entry_id')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'series']);
        });

        Schema::create('asset_repair_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_repair_id');
            $table->unsignedBigInteger('purchase_invoice_id');
            $table->unsignedBigInteger('expense_account_id');
            $table->unsignedBigInteger('payment_account_id');
            $table->string('payment_mode')->nullable();
            $table->decimal('repair_cost', 15, 2);
            $table->timestamps();

            $table->unique(['asset_repair_id', 'purchase_invoice_id'], 'asset_repair_invoice_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_repair_items');
        Schema::dropIfExists('asset_repairs');
    }
};