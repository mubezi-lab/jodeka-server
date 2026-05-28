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
        Schema::create('loan_payments', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | LOAN
            |--------------------------------------------------------------------------
            */

            $table->foreignId('loan_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | PAYMENT
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 15, 2);

            $table->date('payment_date');

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
            | NOTES
            |--------------------------------------------------------------------------
            */

            $table->text('notes')
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
        Schema::dropIfExists('loan_payments');
    }
};