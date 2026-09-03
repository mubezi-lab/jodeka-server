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

    public function removeBypassAndDisconnect(
        NetworkRouter $router,
        string $macAddress
    ): void {
        $client = $this->clients->make($router);
        $macAddress = strtoupper(trim($macAddress));

        $bindings = $client
            ->query(
                (new Query('/ip/hotspot/ip-binding/print'))
                    ->where('mac-address', $macAddress)
            )
            ->read();

        foreach ($bindings as $binding) {
            if (
                strtoupper((string) ($binding['mac-address'] ?? '')) !== $macAddress
                || empty($binding['.id'])
            ) {
                continue;
            }

            $client
                ->query(
                    (new Query('/ip/hotspot/ip-binding/remove'))
                        ->equal('.id', $binding['.id'])
                )
                ->read();
        }

        $hosts = $client
            ->query(
                (new Query('/ip/hotspot/host/print'))
                    ->where('mac-address', $macAddress)
            )
            ->read();

        foreach ($hosts as $host) {
            if (
                strtoupper((string) ($host['mac-address'] ?? '')) !== $macAddress
                || empty($host['.id'])
            ) {
                continue;
            }

            $client
                ->query(
                    (new Query('/ip/hotspot/host/remove'))
                        ->equal('.id', $host['.id'])
                )
                ->read();
        }
    }
}
