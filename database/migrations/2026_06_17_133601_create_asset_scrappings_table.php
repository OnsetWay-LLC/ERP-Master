<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_scrappings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->string('series');

            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('asset_category_id');

            $table->date('scrap_date');

            $table->decimal('asset_cost', 15, 2);
            $table->decimal('accumulated_depreciation_amount', 15, 2)->default(0);
            $table->decimal('book_value_loss', 15, 2)->default(0);

            $table->unsignedBigInteger('fixed_asset_account_id');
            $table->unsignedBigInteger('accumulated_depreciation_account_id');
            $table->unsignedBigInteger('loss_on_disposal_account_id');

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
        Schema::dropIfExists('asset_scrappings');
    }
};