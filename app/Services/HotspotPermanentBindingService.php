<?php

namespace App\Services;

use App\Models\NetworkRouter;
use RouterOS\Query;
use RuntimeException;

class HotspotPermanentBindingService
{
    public function __construct(private MikrotikClientFactory $clients)
    {
    }

    public function ensureBypassed(
        NetworkRouter $router,
        string $macAddress,
        string $name,
        string $userType
    ): void {
        $client = $this->clients->make($router);
        $macAddress = strtoupper(trim($macAddress));
        $comment = trim($name) . ' - '
            . ($userType === 'staff' ? 'Staff' : 'Daily Customer');

        $matches = $client
            ->query(
                (new Query('/ip/hotspot/ip-binding/print'))
                    ->where('mac-address', $macAddress)
            )
            ->read();

        $binding = collect($matches)->first(
            fn (array $item) => strtoupper((string) ($item['mac-address'] ?? '')) === $macAddress
        );

        if ($binding) {
            $bindingId = $binding['.id'] ?? null;

            if (! $bindingId) {
                throw new RuntimeException('MikroTik IP Binding has no identifier.');
            }

            $client
                ->query(
                    (new Query('/ip/hotspot/ip-binding/set'))
                        ->equal('.id', $bindingId)
                        ->equal('type', 'bypassed')
                        ->equal('server', 'all')
                        ->equal('comment', $comment)
                        ->equal('disabled', 'no')
                )
                ->read();

            return;
        }

        $client
            ->query(
                (new Query('/ip/hotspot/ip-binding/add'))
                    ->equal('mac-address', $macAddress)
                    ->equal('type', 'bypassed')
                    ->equal('server', 'all')
                    ->equal('comment', $comment)
            )
            ->read();
    }
}
