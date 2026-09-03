<?php

namespace App\Services;

use App\Models\HotspotPermanentCharge;
use App\Models\HotspotPermanentDailyUsage;
use App\Models\HotspotPermanentUser;
use App\Models\NetworkRouter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RouterOS\Query;
use Throwable;

class HotspotPermanentUsageService
{
    public function __construct(
        private MikrotikClientFactory $clients,
        private HotspotPermanentPaymentService $payments,
        private HotspotPermanentReminderService $reminders
    ) {
    }

    public function syncAll(): array
    {
        $result = ['routers' => 0, 'online' => 0, 'charges_created' => 0, 'errors' => 0];

        $routerIds = HotspotPermanentUser::where('enabled', true)
            ->distinct()
            ->pluck('network_router_id');

        $routers = NetworkRouter::where('enabled', true)
            ->whereIn('id', $routerIds)
            ->get();

        foreach ($routers as $router) {
            try {
                $hosts = $this->clients->make($router)
                    ->query(new Query('/ip/hotspot/host/print'))
                    ->read();

                $hostsByMac = collect($hosts)
                    ->filter(fn ($host) => ! empty($host['mac-address']))
                    ->keyBy(fn ($host) => strtoupper($host['mac-address']));

                $routerResult = $this->syncRouter($router, $hostsByMac);
                $result['routers']++;
                $result['online'] += $routerResult['online'];
                $result['charges_created'] += $routerResult['charges_created'];
            } catch (Throwable $e) {
                $result['errors']++;
                report($e);
            }
        }

        return $result;
    }

    private function syncRouter(NetworkRouter $router, Collection $hostsByMac): array
    {
        $result = ['online' => 0, 'charges_created' => 0];
        $today = now('Africa/Dar_es_Salaam')->toDateString();

        $users = HotspotPermanentUser::where('network_router_id', $router->id)
            ->where('enabled', true)
            ->get();

        foreach ($users as $user) {
            $host = $hostsByMac->get(strtoupper($user->mac_address));

            if (! $host) {
                $user->is_online = false;
                $user->save();

                HotspotPermanentDailyUsage::where('hotspot_permanent_user_id', $user->id)
                    ->whereDate('usage_date', $today)
                    ->update(['is_online' => false]);
                continue;
            }

            $chargeCreated = $this->recordHost($user, $host, $today);
            $result['online']++;
            $result['charges_created'] += $chargeCreated ? 1 : 0;

            if ($user->user_type === 'daily_customer') {
                $this->reminders->queueArrearsWhenOnline($user);
            }
        }

        return $result;
    }

    private function recordHost(
        HotspotPermanentUser $user,
        array $host,
        string $today
    ): bool {
        return DB::transaction(function () use ($user, $host, $today) {
            $usage = HotspotPermanentDailyUsage::firstOrCreate(
                [
                    'hotspot_permanent_user_id' => $user->id,
                    'usage_date' => $today,
                ],
                [
                    'first_seen_at' => now(),
                ]
            );

            $currentIn = (int) ($host['bytes-in'] ?? 0);
            $currentOut = (int) ($host['bytes-out'] ?? 0);
            $hostId = (string) ($host['.id'] ?? '');
            $currentUptime = $this->durationToSeconds($host['uptime'] ?? null);

            $previousIn = (int) $usage->last_bytes_in;
            $previousOut = (int) $usage->last_bytes_out;
            $previousHostId = (string) ($usage->last_host_id ?? '');

            if ($usage->wasRecentlyCreated) {
                $previous = HotspotPermanentDailyUsage::where(
                    'hotspot_permanent_user_id',
                    $user->id
                )
                    ->where('id', '<>', $usage->id)
                    ->latest('usage_date')
                    ->first();

                if ($previous && (string) $previous->last_host_id === $hostId) {
                    $previousIn = (int) $previous->last_bytes_in;
                    $previousOut = (int) $previous->last_bytes_out;
                    $previousHostId = (string) $previous->last_host_id;
                }
            }

            $sameSession = $hostId !== ''
                ? $hostId === $previousHostId
                : $currentUptime >= (int) $usage->last_uptime_seconds
                    && $currentIn >= $previousIn
                    && $currentOut >= $previousOut;
            $deltaIn = $sameSession && $currentIn >= $previousIn
                ? $currentIn - $previousIn
                : $currentIn;
            $deltaOut = $sameSession && $currentOut >= $previousOut
                ? $currentOut - $previousOut
                : $currentOut;

            $usage->bytes_in = (int) $usage->bytes_in + $deltaIn;
            $usage->bytes_out = (int) $usage->bytes_out + $deltaOut;
            $usage->last_bytes_in = $currentIn;
            $usage->last_bytes_out = $currentOut;
            $usage->last_host_id = $hostId;
            $usage->last_ip = $host['address'] ?? $usage->last_ip;
            $usage->last_uptime_seconds = $currentUptime;
            $usage->last_seen_at = now();
            $usage->is_online = true;
            $usage->save();

            $user->is_online = true;
            $user->last_ip = $usage->last_ip;
            $user->last_seen_at = now();
            $user->save();

            if ($user->user_type !== 'daily_customer') {
                return false;
            }

            $totalBytes = (int) $usage->bytes_in + (int) $usage->bytes_out;

            if ($totalBytes < (int) $user->usage_threshold_bytes) {
                return false;
            }

            $charge = HotspotPermanentCharge::firstOrCreate(
                [
                    'hotspot_permanent_user_id' => $user->id,
                    'charge_date' => $today,
                ],
                [
                    'hotspot_permanent_daily_usage_id' => $usage->id,
                    'amount' => $user->daily_rate,
                    'status' => 'unpaid',
                ]
            );

            if ($charge->wasRecentlyCreated && (float) $user->credit_balance > 0) {
                $this->payments->applyAvailableCredit($user->fresh());
            }

            return $charge->wasRecentlyCreated;
        });
    }

    private function durationToSeconds(?string $duration): int
    {
        if (! $duration) {
            return 0;
        }

        preg_match_all('/(\d+)(w|d|h|m|s)/', strtolower($duration), $matches, PREG_SET_ORDER);
        $units = ['w' => 604800, 'd' => 86400, 'h' => 3600, 'm' => 60, 's' => 1];

        return array_sum(array_map(
            fn ($match) => (int) $match[1] * $units[$match[2]],
            $matches
        ));
    }
}
