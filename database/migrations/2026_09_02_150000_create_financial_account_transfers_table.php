<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_account_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->foreignId('from_financial_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->foreignId('to_financial_account_id')->constrained('financial_accounts')->restrictOnDelete();
            $table->decimal('amount', 18, 2);
            $table->decimal('confirmed_amount', 18, 2)->nullable();
            $table->decimal('variance', 18, 2)->nullable();
            $table->date('transfer_date');
            $table->string('external_reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('review_notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('submitted_by')->nullable();
            $table->integer('reviewed_by')->nullable();
            $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['transfer_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_account_transfers');
    }
};
