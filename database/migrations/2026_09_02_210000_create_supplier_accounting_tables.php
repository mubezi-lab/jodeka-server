<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_number')->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('business_id')->constrained()->nullOnDelete();
            $table->string('payment_type', 20)->default('credit')->after('supplier');
            $table->foreignId('payment_financial_account_id')->nullable()->after('payment_type')->constrained('financial_accounts')->nullOnDelete();
        });

        Schema::create('supplier_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->foreignId('goods_receipt_id')->unique()->constrained()->restrictOnDelete();
            $table->decimal('original_amount', 18, 2);
            $table->decimal('balance', 18, 2);
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('unpaid');
            $table->foreignId('journal_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('created_by');
            $table->timestamps();
            $table->foreign('created_by', 'supplier_bills_created_by_fk')->references('id')->on('users')->restrictOnDelete();
        });

        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_bill_id')->constrained()->restrictOnDelete();
            $table->string('payment_number')->unique();
            $table->foreignId('financial_account_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 18, 2);
            $table->date('payment_date');
            $table->string('payment_method', 30)->default('cash');
            $table->string('external_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('journal_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('paid_by');
            $table->timestamps();
            $table->foreign('paid_by', 'supplier_payments_paid_by_fk')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
        Schema::dropIfExists('supplier_bills');
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_financial_account_id');
            $table->dropColumn('payment_type');
            $table->dropConstrainedForeignId('supplier_id');
        });
        Schema::dropIfExists('suppliers');
    }
};
