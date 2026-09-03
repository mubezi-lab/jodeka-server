<?php

namespace Tests\Feature;

use App\Models\HotspotPayment;
use App\Models\HotspotPermanentCharge;
use App\Models\HotspotPermanentUser;
use App\Services\HotspotPermanentPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HotspotPermanentPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registered_daily_customer_payment_settles_oldest_charge_without_voucher(): void
    {
        $this->insertRouter();

        $user = HotspotPermanentUser::create([
            'network_router_id' => 999,
            'name' => 'Kiloba',
            'phone' => '0768848012',
            'normalized_phone' => '255768848012',
            'mac_address' => 'BE:D3:10:DD:A2:13',
            'user_type' => 'daily_customer',
            'daily_rate' => 500,
            'usage_threshold_bytes' => 1048576,
            'enabled' => true,
        ]);

        $charge = HotspotPermanentCharge::create([
            'hotspot_permanent_user_id' => $user->id,
            'charge_date' => '2026-09-02',
            'amount' => 500,
            'status' => 'unpaid',
        ]);

        $payment = HotspotPayment::create([
            'provider' => 'YAS',
            'amount' => 500,
            'payer_phone' => '255768848012',
            'payer_name' => 'KILOBA',
            'reference' => 'PERMANENT-TEST-1',
            'paid_at' => '2026-09-03 15:30:00',
            'raw_sms' => 'Test payment SMS',
            'status' => 'permanent_pending',
        ]);

        $service = app(HotspotPermanentPaymentService::class);
        $matched = $service->findDailyCustomer('0768848012');
        $permanentPayment = $service->recordSmsPayment($matched, $payment);

        $this->assertSame($user->id, $matched?->id);
        $this->assertSame('500.00', $permanentPayment->allocated_amount);
        $this->assertSame('0.00', $permanentPayment->credit_amount);
        $this->assertSame('paid', $charge->fresh()->status);
        $this->assertSame('permanent_completed', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->voucher_id);
    }

    public function test_staff_and_unregistered_phones_do_not_match_daily_customer_flow(): void
    {
        $this->insertRouter();

        HotspotPermanentUser::create([
            'network_router_id' => 999,
            'name' => 'Staff Member',
            'phone' => '0712345678',
            'normalized_phone' => '255712345678',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'user_type' => 'staff',
            'daily_rate' => 0,
            'enabled' => true,
        ]);

        $service = app(HotspotPermanentPaymentService::class);

        $this->assertNull($service->findDailyCustomer('0712345678'));
        $this->assertNull($service->findDailyCustomer('0755555555'));
    }

    private function insertRouter(): void
    {
        DB::table('network_routers')->insert([
            'id' => 999,
            'name' => 'Test Router',
            'host' => '192.0.2.1',
            'api_port' => 8728,
            'username' => 'test',
            'password' => 'encrypted-test-value',
            'use_ssl' => false,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
