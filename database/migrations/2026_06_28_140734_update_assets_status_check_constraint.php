<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE assets DROP CONSTRAINT CK__assets__status__494F2735
        ");

        DB::statement("
            ALTER TABLE assets ADD CONSTRAINT CK_assets_status
            CHECK (status IN ('draft', 'submitted', 'disposed', 'scrapped', 'sold'))
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE assets DROP CONSTRAINT CK_assets_status
        ");

        DB::statement("
            ALTER TABLE assets ADD CONSTRAINT CK__assets__status__494F2735
            CHECK (status IN ('active', 'disposed', 'scrapped', 'sold'))
        ");
    }
};