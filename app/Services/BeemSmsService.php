<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class BeemSmsService
{
    public function send(array $recipients, string $message): array
    {
        /*
        |--------------------------------------------------------------------------
        | SEND REQUEST TO BEEM
        |--------------------------------------------------------------------------
        */

        $response = Http::withBasicAuth(
            config('services.beem.api_key'),
            config('services.beem.secret_key')
        )
            ->timeout(30)
            ->post('https://apisms.beem.africa/v1/send', [
                'source_addr' => config('services.beem.sender'),
                'encoding' => 0,
                'schedule_time' => '',
                'message' => $message,
                'recipients' => $recipients,
            ]);


        /*
        |--------------------------------------------------------------------------
        | HTTP ERROR
        |--------------------------------------------------------------------------
        |
        | 4xx / 5xx responses must not be treated as successfully sent SMS.
        |
        */

        if ($response->failed()) {
            throw new RuntimeException(
                'Beem SMS request failed with HTTP status '
                . $response->status()
                . '.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDATE BEEM RESPONSE
        |--------------------------------------------------------------------------
        */

        $data = $response->json();

        if (! is_array($data)) {
            throw new RuntimeException(
                'Beem SMS returned an invalid response.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | CONFIRM MESSAGE SUBMISSION
        |--------------------------------------------------------------------------
        |
        | Beem successful response currently returns:
        |
        | successful = true
        | code       = 100
        |
        | We require both before considering the SMS successfully submitted.
        |
        */

        $successful = filter_var(
            $data['successful'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        $code = (int) ($data['code'] ?? 0);

        if (! $successful || $code !== 100) {
            throw new RuntimeException(
                'Beem SMS was not submitted successfully. '
                . 'Code: '
                . ($data['code'] ?? 'unknown')
                . '. Message: '
                . ($data['message'] ?? 'Unknown Beem error')
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALID RECIPIENT CHECK
        |--------------------------------------------------------------------------
        */

        if ((int) ($data['valid'] ?? 0) < 1) {
            throw new RuntimeException(
                'Beem SMS response contains no valid recipient.'
            );
        }


        return $data;
    }
}