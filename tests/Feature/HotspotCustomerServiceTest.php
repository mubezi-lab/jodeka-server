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
        $this->createVoucherFixture();

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
        $this->assertSame('JACKSON KAIKA', $customer->payment_name);
        $this->assertSame('payment', $customer->name_source);
        $this->assertSame('JACKSON KAIKA', $customer->display_name);

        $customer->update(['active' => false, 'sms_allowed' => false]);
        $service->syncPayment($payment->fresh());

        $this->assertTrue($customer->fresh()->active);
        $this->assertFalse($customer->fresh()->sms_allowed);
        $this->assertSame(1, $customer->fresh()->total_payments);
    }

    public function test_payment_preserves_manual_name_and_adds_payment_name(): void
    {
        $this->createVoucherFixture();

        $customer = HotspotCustomer::create([
            'name' => 'Kacha',
            'name_source' => 'manual',
            'phone' => '0659840000',
            'normalized_phone' => '255659840000',
            'total_payments' => 0,
            'total_amount' => 0,
            'active' => true,
            'sms_allowed' => true,
        ]);

        $payment = HotspotPayment::create([
            'provider' => 'YAS',
            'amount' => 500,
            'payer_phone' => '255659840000',
            'payer_name' => 'JACKSON KAIKA',
            'reference' => 'CUSTOMER-NICKNAME-1',
            'paid_at' => now(),
            'raw_sms' => 'Test nickname payment',
            'status' => 'completed',
            'voucher_id' => 803,
        ]);

        app(HotspotCustomerService::class)->syncPayment($payment);

        $customer->refresh();
        $this->assertSame('Kacha', $customer->name);
        $this->assertSame('JACKSON KAIKA', $customer->payment_name);
        $this->assertSame('manual', $customer->name_source);
        $this->assertSame('Kacha (JACKSON KAIKA)', $customer->display_name);
        $this->assertSame(1, HotspotCustomer::count());
    }

    public function test_latest_payment_name_updates_payment_created_customer(): void
    {
        $this->createVoucherFixture();

        $firstPayment = HotspotPayment::create([
            'provider' => 'YAS',
            'amount' => 500,
            'payer_phone' => '0659840000',
            'payer_name' => 'JACKSON KAIKA',
            'reference' => 'PAYMENT-NAME-1',
            'paid_at' => now()->subMinute(),
            'raw_sms' => 'First payment name',
            'status' => 'completed',
            'voucher_id' => 803,
        ]);

        $service = app(HotspotCustomerService::class);
        $service->syncPayment($firstPayment);

        $secondPayment = HotspotPayment::create([
            'provider' => 'YAS',
            'amount' => 500,
            'payer_phone' => '255659840000',
            'payer_name' => 'JACKSON JOSEPH KAIKA',
            'reference' => 'PAYMENT-NAME-2',
            'paid_at' => now(),
            'raw_sms' => 'Updated payment name',
            'status' => 'completed',
            'voucher_id' => 803,
        ]);

        $service->syncPayment($secondPayment);

        $customer = HotspotCustomer::sole();
        $this->assertSame('JACKSON JOSEPH KAIKA', $customer->name);
        $this->assertSame('JACKSON JOSEPH KAIKA', $customer->payment_name);
        $this->assertSame('payment', $customer->name_source);
        $this->assertSame('JACKSON JOSEPH KAIKA', $customer->display_name);
        $this->assertSame(2, $customer->total_payments);
    }

    private function createVoucherFixture(): void
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
    }
}
