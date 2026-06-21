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

    $table->decimal('paid_amount',15,2)
        ->default(0);

    $table->decimal('outstanding_amount',15,2)
        ->default(0);

    $table->enum('payment_status',[
        'unpaid',
        'partially_paid',
        'paid'
    ])->default('unpaid');

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('sales_invoices', function (Blueprint $table) {
        $table->dropColumn([
            'paid_amount',
            'outstanding_amount',
            'payment_status',
        ]);
    });
}
};
