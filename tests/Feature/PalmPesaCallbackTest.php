<?php

namespace Tests\Feature;

use App\Models\HotspotPayment;
use App\Models\PalmPesaCallbackEvent;
use App\Models\PalmPesaTransaction;
use App\Services\HotspotPaymentRecoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class PalmPesaCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.palmpesa.base_url' =>
                'https://palmpesa.co.tz',

            'services.palmpesa.api_token' =>
                'test-token',
        ]);
    }

    public function test_non_completed_payment_does_not_create_hotspot_payment(): void
    {
        $transaction = PalmPesaTransaction::create([
            'transaction_id' => 'JDK-HS-TEST-001',
            'order_id' => 'ORDER-TEST-001',
            'amount' => 500,
            'currency' => 'TZS',
            'payer_phone' => '255659840000',
            'payer_name' => 'Test Customer',
            'payer_email' => 'customer@example.com',
            'status' => 'INITIATING',
        ]);

        Http::fake([
            'https://palmpesa.co.tz/api/v2/async/payments/ORDER-TEST-001'
                => Http::response([
                    'success' => true,
                    'data' => [
                        'transaction_id' =>
                            'JDK-HS-TEST-001',

                        'order_id' =>
                            'ORDER-TEST-001',

                        'status' =>
                            'REJECTED',

                        'amount' =>
                            500,

                        'currency' =>
                            'TZS',

                        'reference' =>
                            null,

                        'channel' =>
                            null,

                        'failure_reason' =>
                            'Customer rejected payment.',
                    ],
                ], 200),
        ]);

        $response = $this->postJson(
            '/api/palmpesa/callback',
            [
                'event' =>
                    'payment.rejected',

                'event_id' =>
                    'EVENT-TEST-001',

                'transaction_id' =>
                    'JDK-HS-TEST-001',

                'order_id' =>
                    'ORDER-TEST-001',

                'status' =>
                    'REJECTED',

                'amount' =>
                    500,

                'currency' =>
                    'TZS',

                'failure_reason' =>
                    'Customer rejected payment.',
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'payment_completed' => false,
                'status' => 'REJECTED',
            ]);

        $this->assertDatabaseCount(
            'hotspot_payments',
            0
        );

        $this->assertDatabaseHas(
            'palmpesa_transactions',
            [
                'id' => $transaction->id,
                'status' => 'REJECTED',
            ]
        );
    }

    public function test_completed_payment_creates_hotspot_payment(): void
    {
        $transaction = PalmPesaTransaction::create([
            'transaction_id' => 'JDK-HS-TEST-002',
            'order_id' => 'ORDER-TEST-002',
            'amount' => 500,
            'currency' => 'TZS',
            'payer_phone' => '255659840000',
            'payer_name' => 'Test Customer',
            'payer_email' => 'customer@example.com',
            'status' => 'INITIATING',
        ]);

        Http::fake([
            'https://palmpesa.co.tz/api/v2/async/payments/ORDER-TEST-002'
                => Http::response([
                    'success' => true,
                    'data' => [
                        'transaction_id' =>
                            'JDK-HS-TEST-002',

                        'order_id' =>
                            'ORDER-TEST-002',

                        'status' =>
                            'COMPLETED',

                        'amount' =>
                            500,

                        'currency' =>
                            'TZS',

                        'reference' =>
                            'PALM-REF-002',

                        'channel' =>
                            'TIGOPESATZ',

                        'failure_reason' =>
                            null,
                    ],
                ], 200),
        ]);

        $recovery = Mockery::mock(
            HotspotPaymentRecoveryService::class
        );

        $recovery
            ->shouldReceive('recover')
            ->once()
            ->andReturn([
                'recovered' => true,
                'already_completed' => false,
            ]);

        $this->app->instance(
            HotspotPaymentRecoveryService::class,
            $recovery
        );

        $response = $this->postJson(
            '/api/palmpesa/callback',
            [
                'event' =>
                    'payment.completed',

                'event_id' =>
                    'EVENT-TEST-002',

                'transaction_id' =>
                    'JDK-HS-TEST-002',

                'order_id' =>
                    'ORDER-TEST-002',

                'status' =>
                    'COMPLETED',

                'amount' =>
                    500,

                'currency' =>
                    'TZS',

                'selcom_reference' =>
                    'PALM-REF-002',

                'channel' =>
                    'TIGOPESATZ',
            ]
        );

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'payment_completed' => true,
                'voucher_ready' => true,
            ]);

        $this->assertDatabaseCount(
            'hotspot_payments',
            1
        );

        $payment = HotspotPayment::first();

        $this->assertNotNull($payment);

        $this->assertSame(
            'PALMPESA',
            $payment->provider
        );

        $this->assertSame(
            'PP-JDK-HS-TEST-002',
            $payment->reference
        );

        $this->assertDatabaseHas(
            'palmpesa_transactions',
            [
                'id' => $transaction->id,
                'status' => 'COMPLETED',
                'hotspot_payment_id' => $payment->id,
            ]
        );
    }

    public function test_duplicate_completed_callback_is_not_processed_twice(): void
    {
        PalmPesaTransaction::create([
            'transaction_id' => 'JDK-HS-TEST-003',
            'order_id' => 'ORDER-TEST-003',
            'amount' => 500,
            'currency' => 'TZS',
            'payer_phone' => '255659840000',
            'payer_name' => 'Test Customer',
            'payer_email' => 'customer@example.com',
            'status' => 'INITIATING',
        ]);

        Http::fake([
            'https://palmpesa.co.tz/api/v2/async/payments/ORDER-TEST-003'
                => Http::response([
                    'success' => true,
                    'data' => [
                        'transaction_id' =>
                            'JDK-HS-TEST-003',

                        'order_id' =>
                            'ORDER-TEST-003',

                        'status' =>
                            'COMPLETED',

                        'amount' =>
                            500,

                        'currency' =>
                            'TZS',

                        'reference' =>
                            'PALM-REF-003',

                        'channel' =>
                            'MPESA',

                        'failure_reason' =>
                            null,
                    ],
                ], 200),
        ]);

        $recovery = Mockery::mock(
            HotspotPaymentRecoveryService::class
        );

        $recovery
            ->shouldReceive('recover')
            ->once()
            ->andReturn([
                'recovered' => true,
                'already_completed' => false,
            ]);

        $this->app->instance(
            HotspotPaymentRecoveryService::class,
            $recovery
        );

        $payload = [
            'event' =>
                'payment.completed',

            'event_id' =>
                'EVENT-TEST-003',

            'transaction_id' =>
                'JDK-HS-TEST-003',

            'order_id' =>
                'ORDER-TEST-003',

            'status' =>
                'COMPLETED',

            'amount' =>
                500,

            'currency' =>
                'TZS',

            'selcom_reference' =>
                'PALM-REF-003',

            'channel' =>
                'MPESA',
        ];

        $first = $this->postJson(
            '/api/palmpesa/callback',
            $payload
        );

        $first->assertOk();

        $second = $this->postJson(
            '/api/palmpesa/callback',
            $payload
        );

        $second
            ->assertOk()
            ->assertJson([
                'success' => true,
                'duplicate' => true,
            ]);

        $this->assertDatabaseCount(
            'hotspot_payments',
            1
        );

        $this->assertDatabaseCount(
            'palmpesa_callback_events',
            1
        );

        $this->assertSame(
            1,
            PalmPesaCallbackEvent::count()
        );
    }
}