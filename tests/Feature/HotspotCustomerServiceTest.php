<?php

namespace Tests\Feature;

use App\Models\HotspotCustomer;
use App\Models\HotspotPayment;
use App\Services\HotspotCustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HotspotCustomerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_voucher_payment_creates_one_customer_and_is_idempotent(): void
    {
        DB::table('network_routers')->insert([
            'id' => 801, 'name' => 'Router', 'host' => '192.0.2.1',
            'api_port' => 8728, 'username' => 'test', 'password' => 'test',
            'use_ssl' => false, 'enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('hotspot_profiles')->insert([
            'id' => 802, 'network_router_id' => 801, 'name' => '500TSH-12HRS',
            'mikrotik_profile' => '500TSH-12HRS', 'price' => 500,
            'validity_hours' => 12, 'validity_value' => 12,
            'validity_unit' => 'hours', 'enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('hotspot_vouchers')->insert([
            'id' => 803, 'network_router_id' => 801, 'hotspot_profile_id' => 802,
            'username' => 'JDKCUSTOMER', 'password' => 'JDKCUSTOMER',
            'price' => 500, 'status' => 'unused',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $payment = HotspotPayment::create([
            'provider' => 'YAS', 'amount' => 500,
            'payer_phone' => '0659840000', 'payer_name' => 'JACKSON KAIKA',
            'reference' => 'CUSTOMER-REGISTRY-1', 'paid_at' => now(),
            'raw_sms' => 'Test payment', 'status' => 'completed', 'voucher_id' => 803,
        ]);

        $service = app(HotspotCustomerService::class);
        $service->syncPayment($payment);
        $service->syncPayment($payment->fresh());

        $customer = HotspotCustomer::sole();
        $this->assertSame('255659840000', $customer->normalized_phone);
        $this->assertSame(1, $customer->total_payments);
        $this->assertSame('500.00', $customer->total_amount);
        $this->assertSame($customer->id, $payment->fresh()->hotspot_customer_id);
    }
}
