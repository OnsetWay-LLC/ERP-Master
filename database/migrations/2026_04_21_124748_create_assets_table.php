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
        Schema::create('assets', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained()->cascadeOnDelete();

    $table->foreignId('asset_category_id')->constrained()->noActionOnDelete();
    $table->foreignId('location_id')->nullable()->constrained('asset_locations')->noActionOnDelete();

    $table->string('asset_name_ar');
    $table->string('asset_name_en');

    $table->decimal('purchase_cost', 18, 2);

    $table->date('purchase_date');

    $table->decimal('salvage_value', 18, 2)->default(0);

    $table->string('status')->default('active'); // active, disposed

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
