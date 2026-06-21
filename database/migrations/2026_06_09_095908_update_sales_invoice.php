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
        Schema::table('sales_invoices', function (Blueprint $table) {

    $table->decimal('credit_note_amount',15,2)
        ->default(0);

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
