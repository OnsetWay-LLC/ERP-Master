<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->string('year_name');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('closing_date');

            $table->string('status')->default('open'); 
            // open, closed, permanently_closed

            $table->date('grace_period_end')->nullable();

            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'year_name', 'deleted_at'], 'financial_year_company_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};