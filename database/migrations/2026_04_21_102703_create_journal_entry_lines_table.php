<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();

            // ✅ التصحيح: حذفنا restrictOnDelete() من جميع العلاقات
            // SQL Server سيعتمد الافتراضي (NO ACTION) الذي يمنع الحذف تلقائياً
            $table->foreignId('company_id')->constrained('companies');
            $table->foreignId('journal_entry_id')->constrained('journal_entries');
            $table->foreignId('account_id')->constrained('chart_of_accounts');

            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'account_id']);
            $table->index(['company_id', 'journal_entry_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};