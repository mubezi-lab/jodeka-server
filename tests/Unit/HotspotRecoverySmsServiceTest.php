<?php

namespace Tests\Unit;

use App\Models\HotspotPayment;
use App\Models\HotspotProfile;
use App\Models\HotspotVoucher;
use App\Services\BeemSmsService;
use App\Services\HotspotVoucherSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HotspotRecoverySmsServiceTest extends TestCase
{
    use RefreshDatabase;
    public function test_recovery_message_contains_date_and_voucher_and_uses_one_sms(): void
    {
        config([
            'services.beem.api_key' => 'test-key',
            'services.beem.secret_key' => 'test-secret',
            'services.hotspot_beem.sender' => 'JODEKA',
        ]);

        Http::fake([
            'https://apisms.beem.africa/v1/send' => Http::response([
                'successful' => true,
                'code' => 100,
                'valid' => 1,
                'message' => 'Request successful',
            ], 200),
        ]);

        $payment = new HotspotPayment([
            'amount' => 500,
            'payer_phone' => '0659840000',
            'paid_at' => Carbon::parse('2026-09-19 16:10:00'),
            'voucher_sms_kind' => 'recovery',
        ]);
        $payment->id = 51;

        $voucher = new HotspotVoucher(['username' => 'JDK12345']);
        $profile = new HotspotProfile(['name' => '500TSH-12HRS']);

        (new HotspotVoucherSmsService(new BeemSmsService()))
            ->send($payment, $voucher, $profile);

        Http::assertSent(function ($request) {
            $message = $request['message'];

            return $request['source_addr'] === 'JODEKA'
                && str_contains($message, '19/09/26')
                && str_contains($message, 'JDK12345')
                && str_contains($message, 'JODEKA Hotspot inakuomba radhi')
                && mb_strlen($message) <= 160;
        });
    }
}
