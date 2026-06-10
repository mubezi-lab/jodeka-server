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
        Schema::create('loans', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | BUSINESS
            |--------------------------------------------------------------------------
            */

            $table->foreignId('business_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | LOAN TYPE
            |--------------------------------------------------------------------------
            */

            // payable = tunadaiwa kulipa
            // receivable = tunadaiwa kulipwa

            $table->enum('type', [
                'payable',
                'receivable'
            ]);

            /*
            |--------------------------------------------------------------------------
            | PERSON / COMPANY
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('phone')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | FINANCIALS
            |--------------------------------------------------------------------------
            */

            $table->decimal('amount', 15, 2);

            $table->decimal('paid_amount', 15, 2)
                ->default(0);

            $table->decimal('remaining_amount', 15, 2);

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'pending',
                'partial',
                'paid'
            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | DATES
            |--------------------------------------------------------------------------
            */

            $table->date('loan_date');

            $table->date('due_date')
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

            $table->integer('created_by')
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};