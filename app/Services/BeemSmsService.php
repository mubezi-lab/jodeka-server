<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BeemSmsService
{
    public function send($recipients, $message)
    {
        $response = Http::withBasicAuth(
            config('services.beem.api_key'),
            config('services.beem.secret_key')
        )->post('https://apisms.beem.africa/v1/send', [
            'source_addr' => config('services.beem.sender'),
            'encoding' => 0,
            'schedule_time' => '',
            'message' => $message,
            'recipients' => $recipients,
        ]);

        return $response->json();
    }
}