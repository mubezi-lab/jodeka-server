<?php

namespace Tests\Feature;

use App\Jobs\SendHotspotVoucherSmsJob;
use App\Models\HotspotPayment;
use App\Models\HotspotProfile;
use App\Models\HotspotVoucher;
use App\Services\HotspotCustomerService;
use App\Services\HotspotPaymentRecoveryService;
use App\Services\HotspotVoucherGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class HotspotPaymentRecoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_recovers_payment_once_links_customer_and_queues_sms(): void
    {
        Queue::fake();

        DB::table('network_routers')->insert([
            'id' => 901, 'name' => 'Router', 'host' => '192.0.2.1',
            'api_port' => 8728, 'username' => 'test', 'password' => 'test',
            'use_ssl' => false, 'enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('hotspot_profiles')->insert([
            'id' => 902, 'network_router_id' => 901, 'name' => '500TSH-12HRS',
            'mikrotik_profile' => '500TSH-12HRS', 'price' => 500,
            'validity_hours' => 12, 'validity_value' => 12,
            'validity_unit' => 'hours', 'enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('hotspot_vouchers')->insert([
            'id' => 903, 'network_router_id' => 901, 'hotspot_profile_id' => 902,
            'username' => 'JDKRECOVER', 'password' => 'JDKRECOVER',
            'price' => 500, 'status' => 'unused',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $payment = HotspotPayment::create([
            'provider' => 'HALOPESA', 'amount' => 500,
            'hotspot_profile_id' => 902,
            'payer_phone' => '0625921260', 'payer_name' => 'ANUARI ABDALLAH MGANGA',
            'reference' => '26406829593343', 'paid_at' => now()->subDay(),
            'raw_sms' => 'Test recovery payment', 'status' => 'waiting_for_router',
        ]);

        $voucher = HotspotVoucher::findOrFail(903);
        Cache::put(
            HotspotPaymentRecoveryService::routerReadyCacheKey(901),
            true,
            now()->addMinutes(2)
        );

        $generator = Mockery::mock(HotspotVoucherGenerator::class);
        $generator->shouldReceive('generate')->once()->andReturn($voucher);

        $service = new HotspotPaymentRecoveryService(
            $generator,
            app(HotspotCustomerService::class)
        );

        $first = $service->recover($payment);
        $second = $service->recover($payment->fresh());

        $this->assertTrue($first['recovered']);
        $this->assertTrue($second['already_completed']);
        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertSame(903, $payment->fresh()->voucher_id);
        $this->assertSame('recovery', $payment->fresh()->voucher_sms_kind);
        $this->assertNotNull($payment->fresh()->hotspot_customer_id);
        Queue::assertPushed(SendHotspotVoucherSmsJob::class, 1);
    }

    public function test_it_does_not_attempt_recovery_before_sync_confirms_router(): void
    {
        Queue::fake();

        DB::table('network_routers')->insert([
            'id' => 911, 'name' => 'Offline Router', 'host' => '192.0.2.2',
            'api_port' => 8728, 'username' => 'test', 'password' => 'test',
            'use_ssl' => false, 'enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('hotspot_profiles')->insert([
            'id' => 912, 'network_router_id' => 911, 'name' => '500TSH-12HRS',
            'mikrotik_profile' => '500TSH-12HRS', 'price' => 500,
            'validity_hours' => 12, 'validity_value' => 12,
            'validity_unit' => 'hours', 'enabled' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $payment = HotspotPayment::create([
            'provider' => 'YAS', 'amount' => 500,
            'hotspot_profile_id' => 912,
            'payer_phone' => '255625921260', 'payer_name' => 'ANUARI ABDALLAH MGANGA',
            'reference' => 'OFFLINE-RECOVERY-TEST', 'paid_at' => now()->subDay(),
            'raw_sms' => 'Offline recovery test', 'status' => 'waiting_for_router',
        ]);

        Cache::forget(HotspotPaymentRecoveryService::routerReadyCacheKey(911));

        $generator = Mockery::mock(HotspotVoucherGenerator::class);
        $generator->shouldNotReceive('generate');

        $service = new HotspotPaymentRecoveryService(
            $generator,
            app(HotspotCustomerService::class)
        );

        $result = $service->recover($payment);
        $payment->refresh();

        $this->assertFalse($result['recovered']);
        $this->assertStringContainsString('not been confirmed', $result['error']);
        $this->assertSame('waiting_for_router', $payment->status);
        $this->assertSame(0, (int) $payment->voucher_recovery_attempts);
        $this->assertNull($payment->voucher_recovery_last_attempt_at);
        $this->assertNull($payment->voucher_id);
        Queue::assertNothingPushed();
    }
}
