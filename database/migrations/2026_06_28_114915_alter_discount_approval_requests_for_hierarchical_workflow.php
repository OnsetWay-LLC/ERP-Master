<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            DECLARE @constraintName NVARCHAR(200);

            SELECT @constraintName = cc.name
            FROM sys.check_constraints cc
            INNER JOIN sys.columns c
                ON cc.parent_object_id = c.object_id
            INNER JOIN sys.tables t
                ON cc.parent_object_id = t.object_id
            WHERE t.name = 'discount_approval_requests'
              AND c.name = 'status'
              AND cc.definition LIKE '%status%';

            IF @constraintName IS NOT NULL
                EXEC('ALTER TABLE discount_approval_requests DROP CONSTRAINT ' + @constraintName);
        ");

        Schema::table('discount_approval_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('discount_approval_requests', function (Blueprint $table) {
            $table->string('approval_level')
                ->default('department_manager')
                ->after('allowed_discount_percentage');

            $table->string('status')
                ->default('pending_department_manager_approval')
                ->after('approval_level');

            $table->foreignId('forwarded_by')
                ->nullable()
                ->after('approved_by')
                ->constrained('users')
                ->noActionOnDelete();

            $table->timestamp('forwarded_at')
                ->nullable()
                ->after('responded_at');
        });
    }

    public function down(): void
    {
        Schema::table('discount_approval_requests', function (Blueprint $table) {
            $table->dropForeign(['forwarded_by']);
            $table->dropColumn([
                'approval_level',
                'status',
                'forwarded_by',
                'forwarded_at',
            ]);
        });

        Schema::table('discount_approval_requests', function (Blueprint $table) {
            $table->string('status')->default('pending');
        });
    }
};