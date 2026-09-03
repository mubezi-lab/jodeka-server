<?php

namespace App\Services;

use InvalidArgumentException;

class HotspotPhoneService
{
    public function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '255') && strlen($digits) === 12) {
            return $digits;
        }

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            return '255' . substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '255' . $digits;
        }

        throw new InvalidArgumentException('Namba ya simu si sahihi.');
    }
}
