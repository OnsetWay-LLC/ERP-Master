<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'purchase_invoice_id')) {
                $table->unsignedBigInteger('purchase_invoice_id')->nullable();
            }

            if (! Schema::hasColumn('assets', 'opening_accumulated_depreciation')) {
                $table->decimal('opening_accumulated_depreciation', 15, 2)->default(0);
            }

            if (! Schema::hasColumn('assets', 'opening_number_of_booked_depreciations')) {
                $table->unsignedInteger('opening_number_of_booked_depreciations')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'purchase_invoice_id')) {
                $table->dropColumn('purchase_invoice_id');
            }
        });
    }
};