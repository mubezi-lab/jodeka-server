<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PalmPesaService
{
    /**
     * Palm Pesa API Base URL.
     */
    private function baseUrl(): string
    {
        $baseUrl = (string) config(
            'services.palmpesa.base_url',
            'https://palmpesa.co.tz'
        );

        return rtrim($baseUrl, '/');
    }

    /**
     * Create an authenticated HTTP client.
     */
    private function client(): PendingRequest
    {
        $token = config('services.palmpesa.api_token');

        if (!is_string($token) || trim($token) === '') {
            throw new RuntimeException(
                'Palm Pesa API token is not configured.'
            );
        }

        return Http::withToken(trim($token))
            ->acceptJson()
            ->asJson()
            ->timeout(
                (int) config('services.palmpesa.timeout', 30)
            )
            ->connectTimeout(10);
    }

    /**
     * Initiate a mobile money payment.
     *
     * A successful HTTP 202 response does NOT mean
     * the customer has completed payment.
     */
    public function requestPayment(array $data): array
    {
        $requiredFields = [
            'transaction_id',
            'amount',
            'currency',
            'phone',
            'name',
            'email',
            'callback_url',
        ];

        foreach ($requiredFields as $field) {
            if (
                !array_key_exists($field, $data) ||
                $data[$field] === null ||
                $data[$field] === ''
            ) {
                throw new RuntimeException(
                    "Palm Pesa required field missing: {$field}"
                );
            }
        }

        $response = $this->client()->post(
            $this->baseUrl() . '/api/v2/async/payments',
            $data
        );

        return $this->parseResponse($response);
    }

    /**
     * Check the current payment status using
     * the order_id returned by Palm Pesa.
     */
    public function checkStatus(string $orderId): array
    {
        if (trim($orderId) === '') {
            throw new RuntimeException(
                'Palm Pesa order ID is required.'
            );
        }

        $response = $this->client()->get(
            $this->baseUrl()
            . '/api/v2/async/payments/'
            . rawurlencode($orderId)
        );

        return $this->parseResponse($response);
    }

    /**
     * Parse and validate API responses.
     */
    private function parseResponse(Response $response): array
    {
        $body = $response->json();

        if (!is_array($body)) {
            throw new RuntimeException(
                'Palm Pesa returned an invalid JSON response.'
            );
        }

        if ($response->failed()) {
            $message = $body['message']
                ?? $body['error']
                ?? 'Unknown API error';

            if (!is_string($message)) {
                $message = 'Unknown API error';
            }

            throw new RuntimeException(
                'Palm Pesa API request failed. HTTP '
                . $response->status()
                . ': '
                . $message
            );
        }

        if (($body['success'] ?? false) !== true) {
            throw new RuntimeException(
                'Palm Pesa API did not confirm a successful request.'
            );
        }

        if (!isset($body['data']) || !is_array($body['data'])) {
            throw new RuntimeException(
                'Palm Pesa API response is missing payment data.'
            );
        }

        return $body;
    }
}
