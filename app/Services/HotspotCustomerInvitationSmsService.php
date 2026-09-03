<?php

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

class HotspotCustomerInvitationSmsService
{
    public function __construct(
        private BeemSmsService $beem
    ) {
    }

    public static function message(): string
    {
        return 'Karibu JODEKA Hotspot! Vifurushi TZS '
            . '200/500/1,000/3,000. Lipa Namba 19361296; '
            . 'utatumiwa vocha kwa SMS au ingiza namba '
            . 'uliyolipia kuunganishwa. Asante.';
    }

    public function send(string $phone): array
    {
        $phone = $this->normalizePhone($phone);
        $sender = trim(
            (string) config('services.hotspot_beem.sender')
        );

        if ($sender === '') {
            throw new RuntimeException(
                'HOTSPOT_BEEM_SENDER is not configured.'
            );
        }

        return $this->beem->send([
            [
                'recipient_id' => $phone,
                'dest_addr' => $phone,
            ],
        ], self::message(), $sender);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '255' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '255' . $digits;
        }

        if (! preg_match('/^255\d{9}$/', $digits)) {
            throw new InvalidArgumentException(
                'Invalid hotspot invitation phone number.'
            );
        }

        return $digits;
    }
}
