<?php

namespace App\Services;

use RuntimeException;

class HotspotCustomerBroadcastSmsService
{
    public function __construct(private BeemSmsService $beem)
    {
    }

    public static function message(string $type): string
    {
        return match ($type) {
            'network_back' => 'Habari! Mtandao wa JODEKA Hotspot umerudi na unapatikana sasa. Vifurushi ni TZS 200, 500, 1,000 na 3,000. Lipa Namba 19361296. Karibu tena!',
            'welcome_back' => 'Karibu tena JODEKA Hotspot! Vifurushi ni TZS 200, 500, 1,000 na 3,000. Lipa Namba 19361296; utatumiwa vocha kwa SMS au ingiza namba uliyolipia kuunganishwa.',
            default => throw new RuntimeException('Unknown hotspot customer message type.'),
        };
    }

    public function send(string $phone, string $message): array
    {
        $sender = trim((string) config('services.hotspot_beem.sender'));

        if ($sender === '') {
            throw new RuntimeException('HOTSPOT_BEEM_SENDER is not configured.');
        }

        return $this->beem->send([[
            'recipient_id' => $phone,
            'dest_addr' => $phone,
        ]], $message, $sender);
    }
}
