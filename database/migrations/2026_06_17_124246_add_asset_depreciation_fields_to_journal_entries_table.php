<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('journal_entries', 'asset_id')) {
                $table->unsignedBigInteger('asset_id')->nullable();
            }

            if (! Schema::hasColumn('journal_entries', 'source_type')) {
                $table->string('source_type')->default('manual');
            }
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'asset_id')) {
                $table->dropColumn('asset_id');
            }

            if (Schema::hasColumn('journal_entries', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
};