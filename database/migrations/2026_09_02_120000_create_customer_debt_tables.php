<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_number', 30)->unique();
            $table->string('name');
            $table->string('phone', 30)->nullable()->index();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('credit_limit', 18, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->string('reference', 50)->unique();
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->decimal('original_amount', 18, 2);
            $table->decimal('balance', 18, 2);
            $table->date('debt_date');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('unpaid');
            $table->text('description')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_id', 'status', 'debt_date']);
            $table->index(['customer_id', 'status']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->restrictOnDelete();
            $table->string('payment_number', 50)->unique();
            $table->foreignId('financial_account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 18, 2);
            $table->date('payment_date');
            $table->string('payment_method', 30)->default('cash');
            $table->string('external_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('received_by')->nullable();
            $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['debt_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
        Schema::dropIfExists('debts');
        Schema::dropIfExists('customers');
    }
};
