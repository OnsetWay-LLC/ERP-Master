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
       Schema::create('tax_declaration_settings', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')
        ->constrained('companies')
        ->cascadeOnDelete();

    $table->string('report_month_type')->default('odd');

    $table->foreignId('created_by')->nullable();
    $table->foreignId('updated_by')->nullable();

    $table->timestamps();

    $table->unique('company_id');

    $table->foreign('created_by')
        ->references('id')
        ->on('users')
        ->onDelete('no action');

    $table->foreign('updated_by')
        ->references('id')
        ->on('users')
        ->onDelete('no action');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_declaration_settings');
    }
};
