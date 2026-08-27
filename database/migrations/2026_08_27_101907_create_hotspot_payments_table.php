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
        Schema::create('hotspot_payments', function (Blueprint $table) {
            $table->id();

            // Mtandao/chanzo cha malipo: YAS, MPESA, HALOPESA, n.k.
            $table->string('provider', 30);

            // Kiasi kilicholipwa
            $table->decimal('amount', 15, 2);

            // Taarifa za aliyelipa
            $table->string('payer_phone', 30)->nullable();
            $table->string('payer_name')->nullable();

            // Kumbukumbu No. ya transaction
            // Unique inazuia transaction moja kutumika mara mbili
            $table->string('reference', 100)->unique();

            // Tarehe na muda wa malipo uliopo kwenye SMS
            $table->dateTime('paid_at')->nullable();

            // SMS halisi kwa ajili ya audit/debugging
            $table->text('raw_sms');

            // pending = bado haijatumika kupata voucher
            // redeemed = imeshatumika
            // rejected = malipo yamekataliwa
            $table->string('status', 20)->default('pending');

            // Voucher iliyotolewa baada ya malipo kuthibitishwa
            $table->foreignId('voucher_id')
                ->nullable()
                ->constrained('hotspot_vouchers')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['status', 'amount']);
            $table->index('paid_at');
            $table->index('payer_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotspot_payments');
    }
};
