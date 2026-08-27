<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class HotspotPaymentSmsParser
{
    public function parse(string $sms): array
    {
        $sms = trim(preg_replace('/\s+/', ' ', $sms));

        if (! str_contains($sms, 'Kumbukumbu No.:')) {
            throw new InvalidArgumentException('SMS hii haitambuliki kama SMS ya malipo ya Lipa Kwa Simu.');
        }

        $provider = $this->detectProvider($sms);

        preg_match('/TSh\s*([\d,]+(?:\.\d{1,2})?)/i', $sms, $amountMatch);
        preg_match('/Kumbukumbu No\.:\s*(\d+)/i', $sms, $referenceMatch);
        preg_match('/(\d{2}\/\d{2}\/\d{2})\s+(\d{2}:\d{2})/', $sms, $dateMatch);

        [$payerPhone, $payerName] = $this->extractPayer($sms, $provider);

        if (
            empty($amountMatch[1]) ||
            empty($referenceMatch[1]) ||
            empty($dateMatch[1]) ||
            empty($dateMatch[2])
        ) {
            throw new InvalidArgumentException('Baadhi ya taarifa muhimu hazikupatikana kwenye SMS.');
        }

        $amount = (float) str_replace(',', '', $amountMatch[1]);

        $paidAt = Carbon::createFromFormat(
            'd/m/y H:i',
            $dateMatch[1] . ' ' . $dateMatch[2],
            config('app.timezone')
        );

        return [
            'provider' => $provider,
            'amount' => $amount,
            'payer_phone' => $payerPhone,
            'payer_name' => $payerName,
            'reference' => $referenceMatch[1],
            'paid_at' => $paidAt,
            'raw_sms' => $sms,
            'status' => 'pending',
        ];
    }

    private function detectProvider(string $sms): string
    {
        if (str_contains($sms, 'kutoka kwa Vodacom;')) {
            return 'MPESA';
        }

        if (str_contains($sms, 'kutoka kwa Halopesa;')) {
            return 'HALOPESA';
        }

        if (str_contains($sms, 'kwenye Lipa namba')) {
            return 'YAS';
        }

        return 'UNKNOWN';
    }

    private function extractPayer(string $sms, string $provider): array
    {
        $pattern = match ($provider) {
            'YAS' => '/kutoka kwa\s+(255\d+)\s*-\s*(.+?)\.\s*Kumbukumbu No\.:/i',
            'MPESA', 'HALOPESA' => '/kutoka kwa\s+[^;]+;\s*(255\d+)\s*-\s*(.+?)\s+Kumbukumbu No\.:/i',
            default => null,
        };

        if (! $pattern) {
            return [null, null];
        }

        preg_match($pattern, $sms, $match);

        return [
            $match[1] ?? null,
            isset($match[2]) ? trim($match[2], " .") : null,
        ];
    }
}
