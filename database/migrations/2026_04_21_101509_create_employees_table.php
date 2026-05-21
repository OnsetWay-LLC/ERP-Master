<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('series')->unique();
            $table->string('full_name_ar');
            $table->string('full_name_en')->nullable();
            $table->string('national_id')->unique();

            $table->enum('gender', ['male', 'female']);

            $table->date('date_of_joining');

            $table->enum('status', ['active', 'inactive', 'suspended', 'left'])
                ->default('active');

           $table->foreignId('department_id')
            ->nullable()
            ->constrained('departments')
            ->noActionOnDelete();

             $table->string('mobile_number')->nullable();
             $table->string('company_email')->nullable();
             $table->text('address')->nullable();

            

             $table->enum('marital_status', ['single', 'married'])
                ->nullable();
             $table->string('job_title')->nullable(); 
             $table->enum('wife_working_status', ['working', 'not_working']) ->nullable();
             $table->timestamps();
             $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }

};