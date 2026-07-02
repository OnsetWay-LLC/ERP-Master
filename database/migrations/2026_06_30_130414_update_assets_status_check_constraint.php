<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE assets
            DROP CONSTRAINT CK_assets_status
        ");

        DB::statement("
            ALTER TABLE assets
            ADD CONSTRAINT CK_assets_status
            CHECK (
                status IN (
                    'draft',
                    'submitted',
                    'capitalized',
                    'sold',
                    'scrapped',
                    'cancelled'
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE assets
            DROP CONSTRAINT CK_assets_status
        ");

        DB::statement("
            ALTER TABLE assets
            ADD CONSTRAINT CK_assets_status
            CHECK (
                status IN (
                    'draft',
                    'submitted',
                    'sold',
                    'scrapped',
                    'cancelled'
                )
            )
        ");
    }
};

