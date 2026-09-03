<?php

namespace Tests\Feature;

use App\Services\HotspotCustomerInvitationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HotspotCustomerInvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_selects_unique_expired_customers_who_have_not_paid_today(): void
    {
        Carbon::setTestNow(
            Carbon::create(
                2026,
                9,
                3,
                12,
                0,
                0,
                'Africa/Dar_es_Salaam'
            )
        );

        $this->insertRouterAndProfile();

        $this->insertVoucher(101, 'JDK10101', 'expired');
        $this->insertVoucher(102, 'JDK10102', 'expired');
        $this->insertVoucher(103, 'JDK10103', 'used');
        $this->insertVoucher(104, 'JDK10104', 'expired');
        $this->insertVoucher(105, 'JDK10105', 'expired');
        $this->insertVoucher(106, 'JDK10106', 'used');

        $this->insertPayment(
            201,
            101,
            '255700000001',
            'OLD-1',
            '2026-09-01 10:00:00'
        );
        $this->insertPayment(
            202,
            102,
            '255700000001',
            'OLD-1-DUPLICATE-PHONE',
            '2026-09-02 10:00:00'
        );
        $this->insertPayment(
            203,
            103,
            '255700000002',
            'NOT-EXPIRED',
            '2026-09-01 10:00:00'
        );
        $this->insertPayment(
            204,
            104,
            '255700000003',
            'EXPIRED-BUT-PAID-TODAY',
            '2026-09-01 10:00:00'
        );
        $this->insertPayment(
            205,
            105,
            '255700000004',
            'TODAY-ONLY',
            '2026-09-03 08:00:00'
        );
        $this->insertPayment(
            206,
            106,
            '255700000003',
            'TODAY-NEW-PAYMENT',
            '2026-09-03 09:00:00'
        );

        $phones = app(
            HotspotCustomerInvitationService::class
        )->eligiblePhones();

        $this->assertSame(
            ['255700000001'],
            $phones->all()
        );

        Carbon::setTestNow();
    }

    private function insertRouterAndProfile(): void
    {
        DB::table('network_routers')->insert([
            'id' => 999,
            'name' => 'Test Router',
            'host' => '192.0.2.1',
            'api_port' => 8728,
            'username' => 'test-api-user',
            'password' => 'test-api-password',
            'use_ssl' => false,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('hotspot_profiles')->insert([
            'id' => 999,
            'network_router_id' => 999,
            'name' => 'TEST-500',
            'mikrotik_profile' => 'TEST-500',
            'price' => 500,
            'validity_value' => 12,
            'validity_unit' => 'hours',
            'validity_hours' => 12,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertVoucher(
        int $id,
        string $username,
        string $status
    ): void {
        DB::table('hotspot_vouchers')->insert([
            'id' => $id,
            'network_router_id' => 999,
            'hotspot_profile_id' => 999,
            'username' => $username,
            'password' => $username,
            'price' => 500,
            'status' => $status,
            'source' => 'jodeka',
            'generated_at' => '2026-09-01 09:00:00',
            'used_at' => '2026-09-01 09:05:00',
            'first_login_at' => '2026-09-01 09:05:00',
            'expires_at' => '2026-09-01 21:05:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function insertPayment(
        int $id,
        int $voucherId,
        string $phone,
        string $reference,
        string $paidAt
    ): void {
        DB::table('hotspot_payments')->insert([
            'id' => $id,
            'provider' => 'YAS',
            'amount' => 500,
            'payer_phone' => $phone,
            'payer_name' => 'TEST CUSTOMER',
            'reference' => $reference,
            'paid_at' => $paidAt,
            'raw_sms' => 'Test payment SMS',
            'status' => 'completed',
            'voucher_id' => $voucherId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
