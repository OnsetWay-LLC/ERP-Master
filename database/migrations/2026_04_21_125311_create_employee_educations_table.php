<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_educations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->string('school_university_ar')->nullable();
$table->string('school_university_en')->nullable();
            $table->string('qualification_ar')->nullable();
$table->string('qualification_en')->nullable();
            $table->string('level'); // graduation, post_graduation, under_graduation
            $table->string('year_of_passing')->nullable();
            $table->string('class_percentage')->nullable();
            $table->string('major_ar')->nullable();
$table->string('major_en')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_educations');
    }
};