<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->string('party_type')->nullable()->after('account_id');

            $table->unsignedBigInteger('party_id')->nullable()->after('party_type');

            $table->index(['party_type', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex(['party_type', 'party_id']);
            $table->dropColumn(['party_type', 'party_id']);
        });
    }
};