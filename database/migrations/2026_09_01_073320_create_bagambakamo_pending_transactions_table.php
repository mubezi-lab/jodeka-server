<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bagambakamo_pending_transactions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | MEMBER / BENEFICIARY
            |--------------------------------------------------------------------------
            */

            $table->foreignId('member_id')
                ->nullable()
                ->constrained('bagambakamo_members')
                ->nullOnDelete();

            $table->string('recipient_name')->nullable();

            $table->string('recipient_phone')->nullable();


            /*
            |--------------------------------------------------------------------------
            | M-KOBA TRANSACTION
            |--------------------------------------------------------------------------
            */

            $table->string('reference')->unique();

            $table->decimal('amount', 12, 2);

            $table->dateTime('transaction_date');

            $table->decimal(
                'account_balance',
                12,
                2
            )->nullable();


            /*
            |--------------------------------------------------------------------------
            | RAW SMS
            |--------------------------------------------------------------------------
            */

            $table->text('raw_sms');


            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            |
            | pending   = waiting for admin
            | processed = already classified
            |
            */

            $table->string('status')
                ->default('pending');

            /*
            |--------------------------------------------------------------------------
            | CLASSIFICATION
            |--------------------------------------------------------------------------
            |
            | event
            | expense
            |
            */

            $table->string('classification')
                ->nullable();


            /*
            |--------------------------------------------------------------------------
            | FINAL RECORD
            |--------------------------------------------------------------------------
            |
            | Stores the ID created in events or expenses.
            |
            */

            $table->unsignedBigInteger(
                'processed_record_id'
            )->nullable();

            $table->timestamp(
                'processed_at'
            )->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'bagambakamo_pending_transactions'
        );
    }
};