<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('palmpesa_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotspot_profile_id')
                ->nullable()
                ->constrained('hotspot_profiles')
                ->nullOnDelete();

            $table->foreignId('hotspot_payment_id')
                ->nullable()
                ->unique()
                ->constrained('hotspot_payments')
                ->nullOnDelete();

            $table->string('transaction_id', 191)
                ->unique();

            $table->string('order_id', 191)
                ->nullable()
                ->unique();

            $table->decimal('amount', 15, 2);

            $table->string('currency', 3)
                ->default('TZS');

            $table->string('payer_phone', 30);

            $table->string('payer_name')
                ->nullable();

            $table->string('payer_email')
                ->nullable();

            $table->string('status', 40)
                ->default('INITIATING');

            $table->string('reference', 100)
                ->nullable();

            $table->string('channel', 50)
                ->nullable();

            $table->string('failure_reason')
                ->nullable();

            $table->json('request_payload')
                ->nullable();

            $table->json('response_payload')
                ->nullable();

            $table->timestamp('completed_at')
                ->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('payer_phone');
            $table->index('reference');
            $table->index(['status', 'created_at']);
        });

        Schema::create('palmpesa_callback_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('palmpesa_transaction_id')
                ->nullable()
                ->constrained('palmpesa_transactions')
                ->nullOnDelete();

            $table->string('event_id', 191)
                ->unique();

            $table->string('event', 60);

            $table->string('status', 40);

            $table->json('payload');

            $table->timestamp('received_at');

            $table->timestamp('processed_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'palmpesa_transaction_id',
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('palmpesa_callback_events');
        Schema::dropIfExists('palmpesa_transactions');
    }
};