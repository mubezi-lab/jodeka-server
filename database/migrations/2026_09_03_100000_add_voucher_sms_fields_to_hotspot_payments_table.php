<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->string('voucher_sms_status', 20)
                ->nullable()
                ->after('claimed_by_ip');
            $table->dateTime('voucher_sms_sent_at')
                ->nullable()
                ->after('voucher_sms_status');
            $table->dateTime('voucher_sms_failed_at')
                ->nullable()
                ->after('voucher_sms_sent_at');
            $table->text('voucher_sms_error')
                ->nullable()
                ->after('voucher_sms_failed_at');
            $table->unsignedInteger('voucher_sms_attempts')
                ->default(0)
                ->after('voucher_sms_error');
            $table->json('voucher_sms_response')
                ->nullable()
                ->after('voucher_sms_attempts');

            $table->index('voucher_sms_status');
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->dropIndex(['voucher_sms_status']);
            $table->dropColumn([
                'voucher_sms_status',
                'voucher_sms_sent_at',
                'voucher_sms_failed_at',
                'voucher_sms_error',
                'voucher_sms_attempts',
                'voucher_sms_response',
            ]);
        });
    }
};
