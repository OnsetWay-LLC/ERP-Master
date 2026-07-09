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
    Schema::table('companies', function (Blueprint $table) {
        $table->text('report_header')->nullable();
        $table->text('report_footer')->nullable();
        $table->boolean('show_logo_in_reports')->default(true);
        $table->boolean('show_company_info_in_reports')->default(true);
    });
}

public function down(): void
{
    Schema::table('companies', function (Blueprint $table) {
        $table->dropColumn([
            'report_header',
            'report_footer',
            'show_logo_in_reports',
            'show_company_info_in_reports',
        ]);
    });
}
};
