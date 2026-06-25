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
    Schema::table('fees_templates', function (Blueprint $table) {
        $table->boolean('is_refundable')
            ->default(false)
            ->after('amount');
    });
}

public function down(): void
{
    Schema::table('fees_templates', function (Blueprint $table) {
        $table->dropColumn('is_refundable');
    });
}
};
