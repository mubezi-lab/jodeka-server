<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('account_type', 30);
            $table->string('provider')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('opening_balance', 18, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['business_id', 'name']);
            $table->index(['account_type', 'is_active']);
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('account_type', 30);
            $table->string('normal_balance', 10);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['account_type', 'is_active']);
        });

        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->string('journal_number', 50)->unique();
            $table->date('journal_date');
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            // users.id is a legacy signed INT in the current JODEKA database.
            $table->integer('created_by')->nullable();
            $table->integer('posted_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('posted_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
            $table->index(['business_id', 'journal_date', 'status']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('financial_account_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('debit', 18, 2)->default(0);
            $table->decimal('credit', 18, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['account_id', 'journal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('journals');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('financial_accounts');
    }
};
