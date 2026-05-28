<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run migrations
     */
    public function up(): void
    {
        Schema::create('saving_transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | SAVING
            |--------------------------------------------------------------------------
            */

            $table->foreignId('saving_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TRANSACTION TYPE
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'deposit',

                'withdrawal',

                'transfer',

                'interest',

                'penalty'

            ]);

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
            | DETAILS
            |--------------------------------------------------------------------------
            */

            $table->text('description')
                ->nullable();

            $table->date('transaction_date');

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
     * Reverse migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('saving_transactions');
    }
};