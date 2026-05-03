<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('employee_id')
                ->nullable()
                ->after('id')
                ->constrained('employees')
                ->nullOnDelete();

            $table->string('username')
                ->nullable()
                ->unique()
                ->after('employee_id');

            $table->boolean('is_active')
                ->default(true)
                ->after('password');

            $table->unsignedTinyInteger('failed_attempts')
                ->default(0)
                ->after('is_active');

            $table->timestamp('locked_until')
                ->nullable()
                ->after('failed_attempts');

            $table->boolean('is_initial_admin')->default(false);

        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);

            $table->dropColumn([
                'employee_id',
                'username',
                'is_active',
                'failed_attempts',
                'locked_until',
            ]);
        });
    }
};