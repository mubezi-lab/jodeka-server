<?php

namespace App\Services;

use App\Models\HotspotPayment;
use App\Models\HotspotProfile;
use App\Models\HotspotVoucher;
use InvalidArgumentException;

class HotspotVoucherSmsService
{
    public function __construct(
        private BeemSmsService $beem
    ) {
    }

    public function send(
        HotspotPayment $payment,
        HotspotVoucher $voucher,
        HotspotProfile $profile
    ): array {
        $phone = $this->normalizePhone($payment->payer_phone);

        $amount = number_format(
            (float) $payment->amount,
            0,
            '.',
            ','
        );

        $message = 'Karibu JODEKA Hotspot. Malipo TZS '
            . $amount
            . ' yamepokelewa. Voucher: '
            . $voucher->username
            . '. Kifurushi: '
            . $profile->name
            . '. Ingia kwa namba uliyolipia. Asante.';

        return $this->beem->send([
            [
                'recipient_id' => $payment->id,
                'dest_addr' => $phone,
            ],
        ], $message);
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '0')) {
            $digits = '255' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '255' . $digits;
        }

        if (! preg_match('/^255\d{9}$/', $digits)) {
            throw new InvalidArgumentException(
                'Invalid hotspot SMS recipient phone number.'
            );
        }

        return $digits;
    }
}
