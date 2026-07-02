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
        Schema::table('asset_depreciation_schedules', function (Blueprint $table) {
            $table->decimal('book_value_before', 15, 3)
                ->nullable()
                ->after('depreciation_amount');

            $table->decimal('book_value_after', 15, 3)
                ->nullable()
                ->after('book_value_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_depreciation_schedules', function (Blueprint $table) {
            $table->dropColumn([
                'book_value_before',
                'book_value_after',
            ]);
        });
    }
};