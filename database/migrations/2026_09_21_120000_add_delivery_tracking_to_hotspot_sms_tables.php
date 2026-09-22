<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_manual_sms_messages', function (Blueprint $table) {
            $table->foreignId('hotspot_voucher_id')
                ->nullable()
                ->after('hotspot_customer_id')
                ->constrained('hotspot_vouchers')
                ->nullOnDelete();
            $table->string('message_type', 30)->default('custom')->after('hotspot_voucher_id');
            $table->string('beem_request_id', 50)->nullable()->after('response');
            $table->string('delivery_status', 30)->nullable()->after('beem_request_id');
            $table->dateTime('delivered_at')->nullable()->after('delivery_status');
            $table->unique('hotspot_voucher_id', 'hms_voucher_unique');
            $table->index('beem_request_id', 'hms_beem_request_index');
        });

        Schema::table('hotspot_customer_messages', function (Blueprint $table) {
            $table->string('beem_request_id', 50)->nullable()->after('response');
            $table->string('delivery_status', 30)->nullable()->after('beem_request_id');
            $table->dateTime('delivered_at')->nullable()->after('delivery_status');
            $table->index('beem_request_id', 'hcm_beem_request_index');
        });

        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->string('voucher_sms_request_id', 50)->nullable()->after('voucher_sms_response');
            $table->string('voucher_sms_delivery_status', 30)->nullable()->after('voucher_sms_request_id');
            $table->dateTime('voucher_sms_delivered_at')->nullable()->after('voucher_sms_delivery_status');
            $table->index('voucher_sms_request_id', 'hp_sms_request_index');
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->dropIndex('hp_sms_request_index');
            $table->dropColumn([
                'voucher_sms_request_id',
                'voucher_sms_delivery_status',
                'voucher_sms_delivered_at',
            ]);
        });

        Schema::table('hotspot_customer_messages', function (Blueprint $table) {
            $table->dropIndex('hcm_beem_request_index');
            $table->dropColumn(['beem_request_id', 'delivery_status', 'delivered_at']);
        });

        Schema::table('hotspot_manual_sms_messages', function (Blueprint $table) {
            $table->dropIndex('hms_beem_request_index');
            $table->dropUnique('hms_voucher_unique');
            $table->dropConstrainedForeignId('hotspot_voucher_id');
            $table->dropColumn(['message_type', 'beem_request_id', 'delivery_status', 'delivered_at']);
        });
    }
};
