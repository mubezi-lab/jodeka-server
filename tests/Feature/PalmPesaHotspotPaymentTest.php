<?php

namespace Tests\Feature;

use App\Models\HotspotProfile;
use App\Models\NetworkRouter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PalmPesaHotspotPaymentTest extends TestCase
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

            'services.palmpesa.callback_url' =>
                'https://jodeka.co.tz/api/palmpesa/callback',
        ]);
    }

    public function test_it_can_start_hotspot_palmpesa_payment_without_generating_voucher(): void
    {
        $profile = $this->createProfile(
            price: 500,
            enabled: true
        );

        Http::fake([
            'https://palmpesa.co.tz/api/v2/async/payments'
                => Http::response([
                    'success' => true,
                    'message' => 'Payment accepted.',
                    'data' => [
                        'transaction_id' => null,
                        'order_id' => 'PALM-ORDER-001',
                        'status' => 'INITIATING',
                        'failure_reason' => null,
                        'amount' => 500,
                        'currency' => 'TZS',
                        'reference' => null,
                        'channel' => null,
                        'status_url' =>
                            'https://palmpesa.co.tz/api/v2/async/payments/PALM-ORDER-001',
                    ],
                ], 202),
        ]);

        $response = $this->postJson(
            '/api/hotspot/payments/palmpesa',
            [
                'hotspot_profile_id' => $profile->id,
                'payer_phone' => '0659840000',
                'payer_name' => 'Test Customer',
                'payer_email' => 'customer@example.com',
            ]
        );

        $response
            ->assertStatus(202)
            ->assertJson([
                'success' => true,
                'payment_completed' => false,
                'order_id' => 'PALM-ORDER-001',
                'status' => 'INITIATING',
                'payer_phone' => '255659840000',
                'hotspot_profile_id' => $profile->id,
                'hotspot_profile' => $profile->name,
            ]);

        $this->assertDatabaseCount(
            'palmpesa_transactions',
            1
        );

        $this->assertDatabaseHas(
            'palmpesa_transactions',
            [
                'hotspot_profile_id' => $profile->id,
                'order_id' => 'PALM-ORDER-001',
                'amount' => 500,
                'currency' => 'TZS',
                'payer_phone' => '255659840000',
                'payer_name' => 'Test Customer',
                'payer_email' => 'customer@example.com',
                'status' => 'INITIATING',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        |
        | INITIATING is NOT payment confirmation.
        | No HotspotPayment or voucher may exist yet.
        |
        */

        $this->assertDatabaseCount(
            'hotspot_payments',
            0
        );

        $this->assertDatabaseCount(
            'hotspot_vouchers',
            0
        );

        Http::assertSent(function ($request) use ($profile) {
            $data = $request->data();

            return $request->url()
                    === 'https://palmpesa.co.tz/api/v2/async/payments'

                && $request->hasHeader(
                    'Authorization',
                    'Bearer test-token'
                )

                && isset($data['transaction_id'])

                && str_starts_with(
                    $data['transaction_id'],
                    'JDK-HS-'
                )

                && $data['amount'] === 500

                && $data['currency'] === 'TZS'

                && $data['phone'] === '255659840000'

                && $data['name'] === 'Test Customer'

                && $data['email'] ===
                    'customer@example.com'

                && $data['callback_url'] ===
                    'https://jodeka.co.tz/api/palmpesa/callback'

                && $data['buyer_remarks'] ===
                    'JODEKA Hotspot - ' . $profile->name;
        });
    }

    public function test_customer_cannot_override_hotspot_package_price(): void
    {
        $profile = $this->createProfile(
            price: 1000,
            enabled: true
        );

        Http::fake([
            'https://palmpesa.co.tz/api/v2/async/payments'
                => Http::response([
                    'success' => true,
                    'data' => [
                        'order_id' => 'PALM-ORDER-002',
                        'status' => 'INITIATING',
                        'amount' => 1000,
                        'currency' => 'TZS',
                        'reference' => null,
                        'channel' => null,
                        'failure_reason' => null,
                    ],
                ], 202),
        ]);

        $response = $this->postJson(
            '/api/hotspot/payments/palmpesa',
            [
                'hotspot_profile_id' => $profile->id,

                /*
                | Even if a malicious customer sends an amount,
                | controller must ignore it.
                */
                'amount' => 100,

                'payer_phone' => '255659840000',
                'payer_name' => 'Test Customer',
                'payer_email' => 'customer@example.com',
            ]
        );

        $response->assertStatus(202);

        Http::assertSent(function ($request) {
            return $request['amount'] === 1000;
        });

        $this->assertDatabaseHas(
            'palmpesa_transactions',
            [
                'amount' => 1000,
            ]
        );

        $this->assertDatabaseCount(
            'hotspot_payments',
            0
        );
    }

    public function test_disabled_hotspot_package_cannot_start_payment(): void
    {
        $profile = $this->createProfile(
            price: 500,
            enabled: false
        );

        Http::fake();

        $response = $this->postJson(
            '/api/hotspot/payments/palmpesa',
            [
                'hotspot_profile_id' => $profile->id,
                'payer_phone' => '0659840000',
                'payer_name' => 'Test Customer',
                'payer_email' => 'customer@example.com',
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' =>
                    'Hotspot package haipatikani au imezimwa.',
            ]);

        Http::assertNothingSent();

        $this->assertDatabaseCount(
            'palmpesa_transactions',
            0
        );

        $this->assertDatabaseCount(
            'hotspot_payments',
            0
        );
    }

    public function test_invalid_phone_number_cannot_start_payment(): void
    {
        $profile = $this->createProfile(
            price: 500,
            enabled: true
        );

        Http::fake();

        $response = $this->postJson(
            '/api/hotspot/payments/palmpesa',
            [
                'hotspot_profile_id' => $profile->id,
                'payer_phone' => '12345',
                'payer_name' => 'Test Customer',
                'payer_email' => 'customer@example.com',
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Namba ya simu si sahihi.',
            ]);

        Http::assertNothingSent();

        $this->assertDatabaseCount(
            'palmpesa_transactions',
            0
        );
    }

    private function createProfile(
        int $price,
        bool $enabled
    ): HotspotProfile {
        $router = NetworkRouter::create([
            'name' => 'Test Router',
            'host' => '192.168.88.1',
            'api_port' => 8728,
            'username' => 'test',
            'password' => 'test-password',
            'use_ssl' => false,
            'enabled' => true,
            'location' => 'Test',
            'description' => 'Palm Pesa automated test router',
        ]);

        return HotspotProfile::create([
            'network_router_id' => $router->id,
            'name' => $price . 'TSH-TEST',
            'mikrotik_profile' => 'test-profile',
            'price' => $price,
            'validity_hours' => 12,
            'validity_value' => 12,
            'validity_unit' => 'hours',
            'voucher_prefix' => 'JDK',
            'enabled' => $enabled,
            'description' => 'Palm Pesa automated test package',
        ]);
    }
}