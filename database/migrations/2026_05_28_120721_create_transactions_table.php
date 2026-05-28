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
        Schema::create('transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | BUSINESS
            |--------------------------------------------------------------------------
            | Nullable because some transactions may not belong
            | to a business directly.
            */

            $table->foreignId('business_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TRANSACTION TYPE
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'income',
                'expense',

                'saving_deposit',
                'saving_withdraw',

                'loan_taken',
                'loan_paid',

                'loan_given',
                'loan_received',

                'purchase',
                'sale',

                'transfer'

            ]);

            /*
            |--------------------------------------------------------------------------
            | CATEGORY
            |--------------------------------------------------------------------------
            | Example:
            | sales, salary, rent, toilet_income, eggs, etc
            */

            $table->string('category');

            /*
            |--------------------------------------------------------------------------
            | AMOUNT
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 15, 2);

            /*
            |--------------------------------------------------------------------------
            | PAYMENT METHOD
            |--------------------------------------------------------------------------
            */

            $table->enum('payment_method', [

                'cash',
                'bank',
                'mpesa',
                'airtel_money',
                'mix'

            ])->default('cash');

            /*
            |--------------------------------------------------------------------------
            | DATE
            |--------------------------------------------------------------------------
            */

            $table->date('transaction_date');

            /*
            |--------------------------------------------------------------------------
            | REFERENCE
            |--------------------------------------------------------------------------
            | invoice number, receipt, etc
            */

            $table->string('reference')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | DESCRIPTION
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | CREATED BY
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};