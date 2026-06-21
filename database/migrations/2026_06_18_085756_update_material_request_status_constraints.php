<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE material_requests
            DROP CONSTRAINT CK_material_requests_status
        ");

        DB::statement("
            ALTER TABLE material_requests
            ADD CONSTRAINT CK_material_requests_status
            CHECK (status IN (
                'draft',
                'sent_to_purchase_order',
                'partially_ordered',
                'ordered',
                'completed',
                'cancelled'
            ))
        ");

        DB::statement("
            ALTER TABLE material_request_items
            DROP CONSTRAINT CK_material_request_items_status
        ");

        DB::statement("
            ALTER TABLE material_request_items
            ADD CONSTRAINT CK_material_request_items_status
            CHECK (status IN (
                'pending',
                'partially_ordered',
                'ordered',
                'partially_received',
                'received',
                'cancelled'
            ))
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE material_requests
            DROP CONSTRAINT CK_material_requests_status
        ");

        DB::statement("
            ALTER TABLE material_requests
            ADD CONSTRAINT CK_material_requests_status
            CHECK (status IN (
                'draft',
                'sent_to_purchase_order',
                'completed',
                'cancelled'
            ))
        ");

        DB::statement("
            ALTER TABLE material_request_items
            DROP CONSTRAINT CK_material_request_items_status
        ");

        DB::statement("
            ALTER TABLE material_request_items
            ADD CONSTRAINT CK_material_request_items_status
            CHECK (status IN (
                'pending',
                'ordered',
                'partially_received',
                'received',
                'cancelled'
            ))
        ");
    }
};