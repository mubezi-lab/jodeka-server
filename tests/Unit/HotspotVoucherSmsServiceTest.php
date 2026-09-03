<?php

namespace Tests\Unit;

use App\Models\HotspotPayment;
use App\Models\HotspotProfile;
use App\Models\HotspotVoucher;
use App\Services\BeemSmsService;
use App\Services\HotspotVoucherSmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HotspotVoucherSmsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_hotspot_voucher_to_normalized_payer_phone(): void
    {
        config([
            'services.beem.api_key' => 'test-key',
            'services.beem.secret_key' => 'test-secret',
            'services.beem.sender' => 'JODEKA',
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
        ]);

        $payment->id = 15;

        $voucher = new HotspotVoucher([
            'username' => 'JDK12345',
        ]);

        $profile = new HotspotProfile([
            'name' => '500TSH-12HRS',
        ]);

        $service = new HotspotVoucherSmsService(
            new BeemSmsService()
        );

        $service->send(
            $payment,
            $voucher,
            $profile
        );

        Http::assertSent(function ($request) {
            return $request['source_addr'] === 'JODEKA'
                && $request['recipients'][0]['recipient_id'] === 15
                && $request['recipients'][0]['dest_addr'] === '255659840000'
                && str_contains(
                    $request['message'],
                    'JDK12345'
                )
                && str_contains(
                    $request['message'],
                    '500TSH-12HRS'
                );
        });
    }
}