<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotspot_customers', function (Blueprint $table) {
            $table->dateTime('archived_at')->nullable()->after('active');
            $table->string('archive_reason', 30)->nullable()->after('archived_at');
            $table->dateTime('active_override_until')->nullable()->after('archive_reason');
        });

        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->string('voucher_sms_kind', 20)->nullable()->after('voucher_sms_status');
            $table->unsignedInteger('voucher_recovery_attempts')->default(0)->after('voucher_sms_response');
            $table->dateTime('voucher_recovery_last_attempt_at')->nullable()->after('voucher_recovery_attempts');
            $table->dateTime('voucher_recovery_completed_at')->nullable()->after('voucher_recovery_last_attempt_at');
            $table->text('voucher_recovery_error')->nullable()->after('voucher_recovery_completed_at');

            $table->index(
                ['status', 'voucher_id', 'voucher_recovery_last_attempt_at'],
                'hp_recovery_queue_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->dropIndex('hp_recovery_queue_index');
            $table->dropColumn([
                'voucher_sms_kind',
                'voucher_recovery_attempts',
                'voucher_recovery_last_attempt_at',
                'voucher_recovery_completed_at',
                'voucher_recovery_error',
            ]);
        });

        Schema::table('hotspot_customers', function (Blueprint $table) {
            $table->dropColumn(['archived_at', 'archive_reason', 'active_override_until']);
        });
    }
};
