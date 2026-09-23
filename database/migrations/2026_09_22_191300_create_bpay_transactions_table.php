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
        Schema::create('bpay_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_id')->unique();
            $table->string('reference_number')->unique();

            $table->decimal('amount', 15, 2);

            $table->string('mobile', 30)->nullable();
            $table->string('email')->nullable();

            $table->string('purpose')->nullable();

            $table->string('status')->default('pending')->index();

            $table->text('checkout_url')->nullable();

            $table->timestamp('beem_timestamp')->nullable();
            $table->timestamp('callback_received_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->json('metadata')->nullable();
            $table->json('raw_callback')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpay_transactions');
    }
};