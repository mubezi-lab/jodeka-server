<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class BpayCheckoutService
{
    private string $baseUrl;
    private string $apiKey;
    private string $secretKey;
    private string $secureToken;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            (string) config('services.bpay.base_url'),
            '/'
        );

        $this->apiKey = (string) config('services.bpay.api_key');
        $this->secretKey = (string) config('services.bpay.secret_key');
        $this->secureToken = (string) config('services.bpay.secure_token');

        if (
            $this->baseUrl === '' ||
            $this->apiKey === '' ||
            $this->secretKey === '' ||
            $this->secureToken === ''
        ) {
            throw new RuntimeException(
                'BPAY configuration is incomplete.'
            );
        }
    }

    /**
     * Request a Beem hosted checkout session.
     */
    public function checkout(
        int|float|string $amount,
        string $referenceNumber,
        ?string $mobile = null,
        ?string $email = null,
        ?string $transactionId = null
    ): Response {
        if (! str_starts_with($referenceNumber, 'MUBEZI')) {
            throw new RuntimeException(
                'BPAY reference number must start with MUBEZI.'
            );
        }

        $transactionId ??= (string) Str::uuid();

        $params = [
            'amount' => (string) ((int) $amount),
            'reference_number' => $referenceNumber,
            'transaction_id' => $transactionId,
            'sendSource' => 'true',
        ];

        if ($mobile !== null && $mobile !== '') {
            $params['mobile'] = $mobile;
        }

        if ($email !== null && $email !== '') {
            $params['email'] = $email;
        }

        return Http::withBasicAuth(
            $this->apiKey,
            $this->secretKey
        )
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
                'beem-secure-token' => $this->secureToken,
            ])
            ->timeout(30)
            ->get(
                $this->baseUrl . '/v1/checkout',
                $params
            );
    }

    /**
     * Generate a unique transaction UUID for BPAY.
     */
    public function generateTransactionId(): string
    {
        return (string) Str::uuid();
    }
}