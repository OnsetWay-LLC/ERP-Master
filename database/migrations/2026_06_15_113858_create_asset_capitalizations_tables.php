<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'capitalized_to_asset_id')) {
                $table->unsignedBigInteger('capitalized_to_asset_id')->nullable();
            }

            if (! Schema::hasColumn('assets', 'capitalized_at')) {
                $table->timestamp('capitalized_at')->nullable();
            }
        });

        Schema::create('asset_capitalizations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('series');
            $table->unsignedBigInteger('target_asset_id');
            $table->date('posting_date');
            $table->time('posting_time');
            $table->decimal('consumed_asset_total_value', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, cancelled
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'series']);
        });

        Schema::create('asset_capitalization_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_capitalization_id');
            $table->unsignedBigInteger('asset_id');
            $table->string('asset_name_ar');
            $table->string('asset_name_en');
            $table->string('item_code')->nullable();
            $table->decimal('current_asset_value', 15, 2)->default(0);
            $table->decimal('asset_value', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['asset_capitalization_id', 'asset_id'], 'cap_items_unique_asset');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_capitalization_items');
        Schema::dropIfExists('asset_capitalizations');

        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'capitalized_to_asset_id')) {
                $table->dropColumn('capitalized_to_asset_id');
            }

            if (Schema::hasColumn('assets', 'capitalized_at')) {
                $table->dropColumn('capitalized_at');
            }
        });
    }
};