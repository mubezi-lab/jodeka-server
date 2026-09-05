<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotspot_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('phone', 30);
            $table->string('normalized_phone', 20)->unique();
            $table->dateTime('first_paid_at')->nullable();
            $table->dateTime('last_paid_at')->nullable();
            $table->unsignedInteger('total_payments')->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('sms_allowed')->default(true);
            $table->dateTime('last_sms_at')->nullable();
            $table->timestamps();
            $table->index(['active', 'sms_allowed']);
        });

        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->foreignId('hotspot_customer_id')->nullable()->after('id');
            $table->foreign('hotspot_customer_id', 'hp_customer_fk')
                ->references('id')->on('hotspot_customers')->nullOnDelete();
        });

        Schema::create('hotspot_customer_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotspot_customer_id');
            $table->date('campaign_date');
            $table->string('message_type', 30);
            $table->text('message');
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->text('error')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();
            $table->foreign('hotspot_customer_id', 'hc_message_customer_fk')
                ->references('id')->on('hotspot_customers')->cascadeOnDelete();
            $table->unique(
                ['hotspot_customer_id', 'campaign_date', 'message_type'],
                'hc_message_daily_unique'
            );
            $table->index(['campaign_date', 'status']);
        });

        DB::table('hotspot_payments')
            ->whereNotNull('voucher_id')
            ->whereNotNull('payer_phone')
            ->orderBy('id')
            ->chunkById(200, function ($payments) {
                foreach ($payments as $payment) {
                    $phone = $this->normalizePhone((string) $payment->payer_phone);
                    if (! $phone) continue;

                    $customer = DB::table('hotspot_customers')
                        ->where('normalized_phone', $phone)->first();
                    $now = now();

                    if (! $customer) {
                        $customerId = DB::table('hotspot_customers')->insertGetId([
                            'name' => $payment->payer_name,
                            'phone' => $payment->payer_phone,
                            'normalized_phone' => $phone,
                            'first_paid_at' => $payment->paid_at,
                            'last_paid_at' => $payment->paid_at,
                            'total_payments' => 1,
                            'total_amount' => $payment->amount,
                            'active' => true,
                            'sms_allowed' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    } else {
                        $customerId = $customer->id;
                        DB::table('hotspot_customers')->where('id', $customerId)->update([
                            'name' => $payment->payer_name ?: $customer->name,
                            'phone' => $payment->payer_phone,
                            'last_paid_at' => $payment->paid_at,
                            'total_payments' => (int) $customer->total_payments + 1,
                            'total_amount' => (float) $customer->total_amount + (float) $payment->amount,
                            'updated_at' => $now,
                        ]);
                    }

                    DB::table('hotspot_payments')->where('id', $payment->id)
                        ->update(['hotspot_customer_id' => $customerId]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspot_customer_messages');
        Schema::table('hotspot_payments', function (Blueprint $table) {
            $table->dropForeign('hp_customer_fk');
            $table->dropColumn('hotspot_customer_id');
        });
        Schema::dropIfExists('hotspot_customers');
    }

    private function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '255' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '255' . $digits;
        }
        return preg_match('/^255\d{9}$/', $digits) ? $digits : null;
    }
};
