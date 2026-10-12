<?php

namespace Tests\Feature;

use App\Services\PalmPesaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PalmPesaServiceTest extends TestCase
{
    public function test_it_can_request_a_palmpesa_payment(): void
    {
        config([
            'services.palmpesa.base_url' => 'https://palmpesa.co.tz',
            'services.palmpesa.api_token' => 'test-token',
        ]);

        Http::fake([
            'https://palmpesa.co.tz/api/v2/async/payments' => Http::response([
                'success' => true,
                'message' => 'Payment accepted.',
                'data' => [
                    'transaction_id' => 'JDK-TEST-001',
                    'order_id' => 'PALMPESA-ORDER-001',
                    'status' => 'INITIATING',
                    'failure_reason' => null,
                    'amount' => 500,
                    'currency' => 'TZS',
                    'reference' => null,
                    'channel' => null,
                    'created_at' => '2026-10-12 15:00:00',
                    'updated_at' => '2026-10-12 15:00:00',
                    'status_url' => 'https://palmpesa.co.tz/api/v2/async/payments/PALMPESA-ORDER-001',
                ],
            ], 202),
        ]);

        $service = app(PalmPesaService::class);

        $response = $service->requestPayment([
            'transaction_id' => 'JDK-TEST-001',
            'amount' => 500,
            'currency' => 'TZS',
            'phone' => '255659840000',
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'callback_url' => 'https://jodeka.co.tz/api/palmpesa/callback',
            'buyer_remarks' => 'JODEKA Hotspot payment',
            'merchant_remarks' => 'Hotspot',
        ]);

        $this->assertTrue($response['success']);
        $this->assertSame(
            'PALMPESA-ORDER-001',
            $response['data']['order_id']
        );
        $this->assertSame(
            'INITIATING',
            $response['data']['status']
        );

        Http::assertSent(function ($request) {
            return $request->url()
                === 'https://palmpesa.co.tz/api/v2/async/payments'
                && $request->hasHeader(
                    'Authorization',
                    'Bearer test-token'
                )
                && $request['amount'] === 500
                && $request['currency'] === 'TZS';
        });
    }

    public function test_it_can_check_palmpesa_payment_status(): void
    {
        config([
            'services.palmpesa.base_url' => 'https://palmpesa.co.tz',
            'services.palmpesa.api_token' => 'test-token',
        ]);

        Http::fake([
            'https://palmpesa.co.tz/api/v2/async/payments/PALMPESA-ORDER-001'
                => Http::response([
                    'success' => true,
                    'data' => [
                        'transaction_id' => 'JDK-TEST-001',
                        'order_id' => 'PALMPESA-ORDER-001',
                        'status' => 'COMPLETED',
                        'failure_reason' => null,
                        'amount' => 500,
                        'currency' => 'TZS',
                        'reference' => 'TEST-REFERENCE-001',
                        'channel' => 'TIGOPESATZ',
                        'created_at' => '2026-10-12 15:00:00',
                        'updated_at' => '2026-10-12 15:01:00',
                        'status_url' => 'https://palmpesa.co.tz/api/v2/async/payments/PALMPESA-ORDER-001',
                    ],
                ], 200),
        ]);

        $service = app(PalmPesaService::class);

        $response = $service->checkStatus(
            'PALMPESA-ORDER-001'
        );

        $this->assertTrue($response['success']);
        $this->assertSame(
            'COMPLETED',
            $response['data']['status']
        );
        $this->assertSame(
            'TEST-REFERENCE-001',
            $response['data']['reference']
        );
    }
}
