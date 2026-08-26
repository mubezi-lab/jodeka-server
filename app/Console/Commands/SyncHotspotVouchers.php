<?php

namespace App\Console\Commands;

use App\Models\HotspotProfile;
use App\Models\HotspotVoucher;
use App\Models\NetworkRouter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use RouterOS\Client;
use RouterOS\Query;

class SyncHotspotVouchers extends Command
{
    protected $signature = 'hotspot:sync';

    protected $description = 'Sync and import hotspot vouchers from MikroTik';

    public function handle(): int
    {
        $routers = NetworkRouter::where('enabled', true)->get();

        $imported = 0;
        $updated = 0;
        $expired = 0;
        $deletedFromMikrotik = 0;
        $finalUsageImported = 0;

        foreach ($routers as $router) {
            try {
                $client = $this->routerClient($router);

                /*
                |--------------------------------------------------------------------------
                | GET FINAL USAGE SNAPSHOTS
                |--------------------------------------------------------------------------
                |
                | MikroTik on-logout stores final counters using:
                |
                | /system script
                |
                | name:
                | JODEKA-USAGE-JDKxxxxx
                |
                | source:
                | user=JDKxxxxx|uptime=300|bytes-in=...|bytes-out=...
                |
                | This remains available even after the hotspot user itself
                | has been deleted from MikroTik.
                |
                */

                $usageSnapshots = $client
                    ->query(new Query('/system/script/print'))
                    ->read();

                $usageSnapshotsByUsername = collect($usageSnapshots)
                    ->filter(function ($script) {
                        return isset($script['name'])
                            && str_starts_with(
                                strtoupper($script['name']),
                                'JODEKA-USAGE-JDK'
                            );
                    })
                    ->mapWithKeys(function ($script) {
                        $username = substr(
                            $script['name'],
                            strlen('JODEKA-USAGE-')
                        );

                        return [
                            $username => $script,
                        ];
                    });

                /*
                |--------------------------------------------------------------------------
                | GET ALL MIKROTIK HOTSPOT USERS
                |--------------------------------------------------------------------------
                */

                $mikrotikUsers = $client
                    ->query(new Query('/ip/hotspot/user/print'))
                    ->read();

                /*
                |--------------------------------------------------------------------------
                | GET ALL ACTIVE USERS ONCE
                |--------------------------------------------------------------------------
                */

                $activeUsers = $client
                    ->query(new Query('/ip/hotspot/active/print'))
                    ->read();

                $activeUsersByUsername = collect($activeUsers)
                    ->filter(fn ($user) => !empty($user['user']))
                    ->keyBy('user');

                /*
                |--------------------------------------------------------------------------
                | PROCESS ALL MIKROTIK VOUCHER USERS
                |--------------------------------------------------------------------------
                */

                foreach ($mikrotikUsers as $mikrotikUser) {
                    $username = $mikrotikUser['name'] ?? null;

                    if (!$username) {
                        continue;
                    }

                    /*
                    | Only voucher-style JDK users.
                    */
                    if (!str_starts_with(strtoupper($username), 'JDK')) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | MATCH PROFILE
                    |--------------------------------------------------------------------------
                    */

                    $mikrotikProfileName = $mikrotikUser['profile'] ?? null;

                    if (!$mikrotikProfileName) {
                        $this->warn(
                            'Skipped MikroTik voucher '
                            . $username
                            . ': MikroTik profile not found.'
                        );

                        continue;
                    }

                    $profile = HotspotProfile::where(
                        'network_router_id',
                        $router->id
                    )
                        ->where(
                            'mikrotik_profile',
                            $mikrotikProfileName
                        )
                        ->first();

                    if (!$profile) {
                        $this->warn(
                            'Skipped MikroTik voucher '
                            . $username
                            . ': JODEKA profile matching "'
                            . $mikrotikProfileName
                            . '" was not found.'
                        );

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | FIND OR IMPORT VOUCHER
                    |--------------------------------------------------------------------------
                    */

                    $voucher = HotspotVoucher::where(
                        'network_router_id',
                        $router->id
                    )
                        ->where('username', $username)
                        ->first();

                    if (!$voucher) {
                        $voucher = HotspotVoucher::create([
                            'network_router_id' => $router->id,
                            'hotspot_profile_id' => $profile->id,
                            'username' => $username,

                            'password' =>
                                $mikrotikUser['password']
                                ?? $username,

                            'price' => $profile->price ?? 0,

                            'status' => 'unused',

                            'source' => 'mikrotik',

                            /*
                            | MikroTik does not reliably provide
                            | the original voucher creation date.
                            */
                            'generated_at' => null,

                            'generated_by' => null,

                            'comment' =>
                                $mikrotikUser['comment']
                                ?? null,
                        ]);

                        $imported++;

                        $this->info(
                            'Imported MikroTik voucher: '
                            . $voucher->username
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | DO NOT MODIFY FINISHED VOUCHERS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        in_array(
                            $voucher->status,
                            [
                                'expired',
                                'cancelled',
                                'disabled',
                            ],
                            true
                        )
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | READ PERMANENT MIKROTIK USER COUNTERS
                    |--------------------------------------------------------------------------
                    */

                    $userBytesIn =
                        (int) ($mikrotikUser['bytes-in'] ?? 0);

                    $userBytesOut =
                        (int) ($mikrotikUser['bytes-out'] ?? 0);

                    $userPacketsIn =
                        (int) ($mikrotikUser['packets-in'] ?? 0);

                    $userPacketsOut =
                        (int) ($mikrotikUser['packets-out'] ?? 0);

                    $userUptime =
                        $mikrotikUser['uptime'] ?? null;

                    /*
                    |--------------------------------------------------------------------------
                    | DETERMINE WHETHER VOUCHER HAS EVER BEEN USED
                    |--------------------------------------------------------------------------
                    */

                    $hasUsage =
                        $userBytesIn > 0
                        || $userBytesOut > 0
                        || $userPacketsIn > 0
                        || $userPacketsOut > 0
                        || $this->uptimeHasUsage($userUptime);

                    /*
                    |--------------------------------------------------------------------------
                    | UPDATE STORED COUNTERS
                    |--------------------------------------------------------------------------
                    */

                    if ($userBytesIn > 0) {
                        $voucher->bytes_in = max(
                            (int) ($voucher->bytes_in ?? 0),
                            $userBytesIn
                        );
                    }

                    if ($userBytesOut > 0) {
                        $voucher->bytes_out = max(
                            (int) ($voucher->bytes_out ?? 0),
                            $userBytesOut
                        );
                    }

                    if ($userPacketsIn > 0) {
                        $voucher->packets_in = max(
                            (int) ($voucher->packets_in ?? 0),
                            $userPacketsIn
                        );
                    }

                    if ($userPacketsOut > 0) {
                        $voucher->packets_out = max(
                            (int) ($voucher->packets_out ?? 0),
                            $userPacketsOut
                        );
                    }

                    if ($this->uptimeHasUsage($userUptime)) {
                        $voucher->mikrotik_uptime = $userUptime;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | USED STATUS FROM HISTORICAL MIKROTIK COUNTERS
                    |--------------------------------------------------------------------------
                    */

                    if ($hasUsage && $voucher->status === 'unused') {
                        $voucher->status = 'used';
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CHECK WHETHER CURRENTLY ACTIVE
                    |--------------------------------------------------------------------------
                    */

                    $activeUser =
                        $activeUsersByUsername->get($username);

                    if ($activeUser) {

                        /*
                        |--------------------------------------------------------------------------
                        | FIRST LOGIN
                        |--------------------------------------------------------------------------
                        |
                        | We use MikroTik active-session uptime to determine
                        | the real login time instead of using sync time.
                        |
                        */

                        if (!$voucher->first_login_at) {

                            $activeUptime =
                                $activeUser['uptime'] ?? null;

                            $uptimeSeconds =
                                $this->parseMikrotikUptimeToSeconds(
                                    $activeUptime
                                );

                            if ($uptimeSeconds > 0) {
                                $voucher->first_login_at =
                                    now()->subSeconds(
                                        $uptimeSeconds
                                    );
                            } else {
                                /*
                                | Fallback only if MikroTik has not yet
                                | reported a usable uptime.
                                */
                                $voucher->first_login_at = now();
                            }

                            $voucher->used_at =
                                $voucher->first_login_at;

                            if ($profile) {
                                $voucher->expires_at =
                                    $this->calculateExpiry(
                                        $voucher
                                            ->first_login_at
                                            ->copy(),
                                        (int) $profile->validity_value,
                                        $profile->validity_unit
                                    );
                            }
                        }

                        if (!$voucher->used_at) {
                            $voucher->used_at =
                                $voucher->first_login_at
                                ?? now();
                        }

                        $voucher->status = 'used';

                        /*
                        |--------------------------------------------------------------------------
                        | ACTIVE DEVICE INFORMATION
                        |--------------------------------------------------------------------------
                        */

                        $voucher->used_by_ip =
                            $activeUser['address']
                            ?? $voucher->used_by_ip;

                        $voucher->used_by_mac =
                            $activeUser['mac-address']
                            ?? $voucher->used_by_mac;

                        /*
                        |--------------------------------------------------------------------------
                        | ACTIVE SESSION COUNTERS
                        |--------------------------------------------------------------------------
                        */

                        if (isset($activeUser['bytes-in'])) {
                            $voucher->bytes_in = max(
                                (int) ($voucher->bytes_in ?? 0),
                                (int) $activeUser['bytes-in']
                            );
                        }

                        if (isset($activeUser['bytes-out'])) {
                            $voucher->bytes_out = max(
                                (int) ($voucher->bytes_out ?? 0),
                                (int) $activeUser['bytes-out']
                            );
                        }

                        if (isset($activeUser['packets-in'])) {
                            $voucher->packets_in = max(
                                (int) ($voucher->packets_in ?? 0),
                                (int) $activeUser['packets-in']
                            );
                        }

                        if (isset($activeUser['packets-out'])) {
                            $voucher->packets_out = max(
                                (int) ($voucher->packets_out ?? 0),
                                (int) $activeUser['packets-out']
                            );
                        }

                        /*
                        | Prefer permanent user uptime when available.
                        | Otherwise use active-session uptime.
                        */

                        if (!$this->uptimeHasUsage($userUptime)) {
                            if (isset($activeUser['uptime'])) {
                                $voucher->mikrotik_uptime =
                                    $activeUser['uptime'];
                            }
                        }

                        $voucher->last_seen_at = now();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | LAST SYNC
                    |--------------------------------------------------------------------------
                    */

                    $voucher->last_synced_at = now();

                    $voucher->save();

                    $updated++;
                }

                /*
                |--------------------------------------------------------------------------
                | IMPORT FINAL USAGE SNAPSHOTS
                |--------------------------------------------------------------------------
                |
                | These snapshots survive after MikroTik removes the hotspot
                | user. This means JODEKA can still receive the last session
                | counters even if the next cron run happens after expiry.
                |
                */

                foreach (
                    $usageSnapshotsByUsername
                    as $username => $snapshotScript
                ) {
                    try {

                        /*
                        |--------------------------------------------------------------------------
                        | FIND JODEKA VOUCHER
                        |--------------------------------------------------------------------------
                        */

                        $voucher = HotspotVoucher::where(
                            'network_router_id',
                            $router->id
                        )
                            ->where(
                                'username',
                                $username
                            )
                            ->first();

                        /*
                        | Never delete an unknown snapshot.
                        |
                        | If the DB record cannot be found, leave the
                        | snapshot in MikroTik so we can inspect/retry it.
                        */
                        if (!$voucher) {
                            $this->warn(
                                'Usage snapshot found but JODEKA voucher '
                                . 'was not found: '
                                . $username
                            );

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | PARSE SNAPSHOT SOURCE
                        |--------------------------------------------------------------------------
                        */

                        $source =
                            $snapshotScript['source']
                            ?? '';

                        $snapshot =
                            $this->parseUsageSnapshot(
                                $source
                            );

                        if (!$snapshot) {
                            $this->warn(
                                'Invalid usage snapshot for '
                                . $username
                            );

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | VERIFY SNAPSHOT USER
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !empty($snapshot['user'])
                            && strtoupper(
                                $snapshot['user']
                            ) !== strtoupper($username)
                        ) {
                            $this->warn(
                                'Snapshot username mismatch for '
                                . $username
                            );

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | FINAL BYTES
                        |--------------------------------------------------------------------------
                        */

                        $snapshotBytesIn =
                            (int) (
                                $snapshot['bytes-in']
                                ?? 0
                            );

                        $snapshotBytesOut =
                            (int) (
                                $snapshot['bytes-out']
                                ?? 0
                            );

                        if ($snapshotBytesIn > 0) {
                            $voucher->bytes_in = max(
                                (int) ($voucher->bytes_in ?? 0),
                                $snapshotBytesIn
                            );
                        }

                        if ($snapshotBytesOut > 0) {
                            $voucher->bytes_out = max(
                                (int) ($voucher->bytes_out ?? 0),
                                $snapshotBytesOut
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | FINAL PACKETS
                        |--------------------------------------------------------------------------
                        */

                        $snapshotPacketsIn =
                            (int) (
                                $snapshot['packets-in']
                                ?? 0
                            );

                        $snapshotPacketsOut =
                            (int) (
                                $snapshot['packets-out']
                                ?? 0
                            );

                        if ($snapshotPacketsIn > 0) {
                            $voucher->packets_in = max(
                                (int) ($voucher->packets_in ?? 0),
                                $snapshotPacketsIn
                            );
                        }

                        if ($snapshotPacketsOut > 0) {
                            $voucher->packets_out = max(
                                (int) ($voucher->packets_out ?? 0),
                                $snapshotPacketsOut
                            );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | FINAL UPTIME
                        |--------------------------------------------------------------------------
                        |
                        | Our MikroTik snapshot stores uptime in seconds.
                        |
                        */

                        $snapshotUptime =
                            (int) (
                                $snapshot['uptime']
                                ?? 0
                            );

                        if ($snapshotUptime > 0) {
                            $voucher->mikrotik_uptime =
                                $this->secondsToMikrotikUptime(
                                    $snapshotUptime
                                );
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | LAST SYNC
                        |--------------------------------------------------------------------------
                        */

                        $voucher->last_synced_at = now();

                        /*
                        |--------------------------------------------------------------------------
                        | SAVE FIRST
                        |--------------------------------------------------------------------------
                        |
                        | The MikroTik snapshot is NOT deleted until the
                        | database save succeeds.
                        |
                        */

                        $voucher->save();

                        /*
                        |--------------------------------------------------------------------------
                        | REMOVE IMPORTED SNAPSHOT
                        |--------------------------------------------------------------------------
                        */

                        if (isset($snapshotScript['.id'])) {
                            $client
                                ->query(
                                    (new Query(
                                        '/system/script/remove'
                                    ))
                                        ->equal(
                                            '.id',
                                            $snapshotScript['.id']
                                        )
                                )
                                ->read();
                        }

                        $finalUsageImported++;

                        $totalFinalBytes =
                            (int) ($voucher->bytes_in ?? 0)
                            +
                            (int) ($voucher->bytes_out ?? 0);

                        $this->info(
                            'Final usage imported: '
                            . $username
                            . ' ['
                            . $this->formatBytesForConsole(
                                $totalFinalBytes
                            )
                            . ']'
                        );

                    } catch (\Throwable $e) {
                        /*
                        | Do not delete snapshot if something fails.
                        | It will remain available for the next sync.
                        */
                        $this->warn(
                            'Could not import final usage snapshot for '
                            . $username
                            . ': '
                            . $e->getMessage()
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | FIND EXPIRED VOUCHERS
                |--------------------------------------------------------------------------
                */

                $expiredVouchers =
                    HotspotVoucher::with('profile')
                        ->where(
                            'network_router_id',
                            $router->id
                        )
                        ->where('status', 'used')
                        ->whereNotNull('expires_at')
                        ->where(
                            'expires_at',
                            '<=',
                            now()
                        )
                        ->get();

                /*
                |--------------------------------------------------------------------------
                | PROCESS EXPIRED VOUCHERS
                |--------------------------------------------------------------------------
                */

                foreach ($expiredVouchers as $voucher) {
                    try {

                        /*
                        |--------------------------------------------------------------------------
                        | CAPTURE FINAL USER COUNTERS
                        |--------------------------------------------------------------------------
                        |
                        | This remains as an additional fallback.
                        |
                        */

                        $finalUsers = $client
                            ->query(
                                (new Query(
                                    '/ip/hotspot/user/print'
                                ))
                                    ->where(
                                        'name',
                                        $voucher->username
                                    )
                            )
                            ->read();

                        $finalUser =
                            $finalUsers[0]
                            ?? null;

                        if ($finalUser) {
                            if (isset($finalUser['bytes-in'])) {
                                $voucher->bytes_in = max(
                                    (int) ($voucher->bytes_in ?? 0),
                                    (int) $finalUser['bytes-in']
                                );
                            }

                            if (isset($finalUser['bytes-out'])) {
                                $voucher->bytes_out = max(
                                    (int) ($voucher->bytes_out ?? 0),
                                    (int) $finalUser['bytes-out']
                                );
                            }

                            if (isset($finalUser['packets-in'])) {
                                $voucher->packets_in = max(
                                    (int) ($voucher->packets_in ?? 0),
                                    (int) $finalUser['packets-in']
                                );
                            }

                            if (isset($finalUser['packets-out'])) {
                                $voucher->packets_out = max(
                                    (int) ($voucher->packets_out ?? 0),
                                    (int) $finalUser['packets-out']
                                );
                            }

                            if (
                                $this->uptimeHasUsage(
                                    $finalUser['uptime']
                                    ?? null
                                )
                            ) {
                                $voucher->mikrotik_uptime =
                                    $finalUser['uptime'];
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | CAPTURE FINAL ACTIVE SESSION
                        |--------------------------------------------------------------------------
                        */

                        $finalSessions = $client
                            ->query(
                                (new Query(
                                    '/ip/hotspot/active/print'
                                ))
                                    ->where(
                                        'user',
                                        $voucher->username
                                    )
                            )
                            ->read();

                        $finalSession =
                            $finalSessions[0]
                            ?? null;

                        if ($finalSession) {
                            if (isset($finalSession['bytes-in'])) {
                                $voucher->bytes_in = max(
                                    (int) ($voucher->bytes_in ?? 0),
                                    (int) $finalSession['bytes-in']
                                );
                            }

                            if (isset($finalSession['bytes-out'])) {
                                $voucher->bytes_out = max(
                                    (int) ($voucher->bytes_out ?? 0),
                                    (int) $finalSession['bytes-out']
                                );
                            }

                            if (isset($finalSession['packets-in'])) {
                                $voucher->packets_in = max(
                                    (int) ($voucher->packets_in ?? 0),
                                    (int) $finalSession['packets-in']
                                );
                            }

                            if (isset($finalSession['packets-out'])) {
                                $voucher->packets_out = max(
                                    (int) ($voucher->packets_out ?? 0),
                                    (int) $finalSession['packets-out']
                                );
                            }

                            if (isset($finalSession['address'])) {
                                $voucher->used_by_ip =
                                    $finalSession['address'];
                            }

                            if (isset($finalSession['mac-address'])) {
                                $voucher->used_by_mac =
                                    $finalSession['mac-address'];
                            }

                            $voucher->last_seen_at = now();
                        }

                        $voucher->last_synced_at = now();

                        $voucher->save();

                    } catch (\Throwable $e) {
                        $this->warn(
                            'Could not capture final usage for '
                            . $voucher->username
                            . ': '
                            . $e->getMessage()
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | REMOVE FROM MIKROTIK
                    |--------------------------------------------------------------------------
                    */

                    $this->removeVoucherFromMikrotik(
                        $client,
                        $voucher->username
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | KEEP HISTORY IN JODEKA
                    |--------------------------------------------------------------------------
                    */

                    $voucher->status = 'expired';

                    $voucher->disabled_at = now();

                    $voucher->last_synced_at = now();

                    $voucher->save();

                    $expired++;

                    $deletedFromMikrotik++;
                }

                $this->info(
                    'Router synced: '
                    . $router->name
                );

            } catch (\Throwable $e) {
                $this->error(
                    'Router sync failed ['
                    . $router->name
                    . ']: '
                    . $e->getMessage()
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL SUMMARY
        |--------------------------------------------------------------------------
        */

        $this->info(
            'Sync completed. Imported: '
            . $imported
            . ', Updated: '
            . $updated
            . ', Final Usage Imported: '
            . $finalUsageImported
            . ', Expired: '
            . $expired
            . ', Deleted from MikroTik: '
            . $deletedFromMikrotik
        );

        return self::SUCCESS;
    }

    /**
     * Parse final usage snapshot stored inside MikroTik /system script.
     *
     * Example source:
     *
     * user=JDK61624
     * |uptime=300
     * |bytes-in=198461
     * |bytes-out=235836
     * |bytes-total=434297
     * |packets-in=934
     * |packets-out=879
     */
    private function parseUsageSnapshot(
        ?string $source
    ): array {
        if (!$source) {
            return [];
        }

        $result = [];

        foreach (explode('|', $source) as $part) {
            if (!str_contains($part, '=')) {
                continue;
            }

            [$key, $value] =
                array_pad(
                    explode(
                        '=',
                        $part,
                        2
                    ),
                    2,
                    null
                );

            $key =
                trim(
                    (string) $key
                );

            $value =
                trim(
                    (string) $value
                );

            if ($key === '') {
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Convert MikroTik uptime string to seconds.
     *
     * Examples:
     *
     * 52s          = 52 seconds
     * 4m31s        = 271 seconds
     * 1h2m5s       = 3725 seconds
     * 2d3h         = 183600 seconds
     * 1w2d3h4m5s   = total seconds
     */
    private function parseMikrotikUptimeToSeconds(
        ?string $uptime
    ): int {
        if (!$uptime) {
            return 0;
        }

        $uptime = trim($uptime);

        if (
            $uptime === ''
            || $uptime === '0s'
            || $uptime === '00:00:00'
            || $uptime === '0:00:00'
        ) {
            return 0;
        }

        /*
        |--------------------------------------------------------------------------
        | HANDLE HH:MM:SS FORMAT
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/^(\d+):(\d+):(\d+)$/',
                $uptime,
                $matches
            )
        ) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            $seconds = (int) $matches[3];

            return ($hours * 3600)
                + ($minutes * 60)
                + $seconds;
        }

        /*
        |--------------------------------------------------------------------------
        | HANDLE MIKROTIK FORMAT
        |--------------------------------------------------------------------------
        */

        $totalSeconds = 0;

        if (
            preg_match(
                '/(\d+)w/',
                $uptime,
                $matches
            )
        ) {
            $totalSeconds +=
                (int) $matches[1]
                * 604800;
        }

        if (
            preg_match(
                '/(\d+)d/',
                $uptime,
                $matches
            )
        ) {
            $totalSeconds +=
                (int) $matches[1]
                * 86400;
        }

        if (
            preg_match(
                '/(\d+)h/',
                $uptime,
                $matches
            )
        ) {
            $totalSeconds +=
                (int) $matches[1]
                * 3600;
        }

        if (
            preg_match(
                '/(\d+)m/',
                $uptime,
                $matches
            )
        ) {
            $totalSeconds +=
                (int) $matches[1]
                * 60;
        }

        if (
            preg_match(
                '/(\d+)s/',
                $uptime,
                $matches
            )
        ) {
            $totalSeconds +=
                (int) $matches[1];
        }

        return $totalSeconds;
    }

    /**
     * Convert seconds to MikroTik-style uptime.
     *
     * Examples:
     *
     * 300  -> 5m
     * 330  -> 5m30s
     * 3665 -> 1h1m5s
     */
    private function secondsToMikrotikUptime(
        int $seconds
    ): string {
        if ($seconds <= 0) {
            return '0s';
        }

        $weeks =
            intdiv(
                $seconds,
                604800
            );

        $seconds %= 604800;

        $days =
            intdiv(
                $seconds,
                86400
            );

        $seconds %= 86400;

        $hours =
            intdiv(
                $seconds,
                3600
            );

        $seconds %= 3600;

        $minutes =
            intdiv(
                $seconds,
                60
            );

        $seconds %= 60;

        $result = '';

        if ($weeks > 0) {
            $result .=
                $weeks . 'w';
        }

        if ($days > 0) {
            $result .=
                $days . 'd';
        }

        if ($hours > 0) {
            $result .=
                $hours . 'h';
        }

        if ($minutes > 0) {
            $result .=
                $minutes . 'm';
        }

        if ($seconds > 0) {
            $result .=
                $seconds . 's';
        }

        return $result !== ''
            ? $result
            : '0s';
    }

    /**
     * Check whether MikroTik uptime represents actual usage.
     */
    private function uptimeHasUsage(
        ?string $uptime
    ): bool {
        if (!$uptime) {
            return false;
        }

        $uptime =
            trim(
                $uptime
            );

        return !in_array(
            $uptime,
            [
                '',
                '0s',
                '00:00:00',
                '0:00:00',
            ],
            true
        );
    }

    /**
     * Format bytes for console messages.
     */
    private function formatBytesForConsole(
        int $bytes
    ): string {
        if ($bytes <= 0) {
            return '0 MB';
        }

        if ($bytes >= 1073741824) {
            return number_format(
                $bytes / 1073741824,
                2
            ) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format(
                $bytes / 1048576,
                2
            ) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format(
                $bytes / 1024,
                2
            ) . ' KB';
        }

        return number_format(
            $bytes
        ) . ' B';
    }

    /**
     * Remove voucher completely from MikroTik.
     */
    private function removeVoucherFromMikrotik(
        Client $client,
        string $username
    ): void {
        /*
        |--------------------------------------------------------------------------
        | REMOVE ACTIVE SESSION
        |--------------------------------------------------------------------------
        */

        $activeSessions = $client
            ->query(
                (new Query(
                    '/ip/hotspot/active/print'
                ))
                    ->where(
                        'user',
                        $username
                    )
            )
            ->read();

        foreach ($activeSessions as $session) {
            if (!isset($session['.id'])) {
                continue;
            }

            $client
                ->query(
                    (new Query(
                        '/ip/hotspot/active/remove'
                    ))
                        ->equal(
                            '.id',
                            $session['.id']
                        )
                )
                ->read();
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE HOTSPOT COOKIE
        |--------------------------------------------------------------------------
        */

        $cookies = $client
            ->query(
                (new Query(
                    '/ip/hotspot/cookie/print'
                ))
                    ->where(
                        'user',
                        $username
                    )
            )
            ->read();

        foreach ($cookies as $cookie) {
            if (!isset($cookie['.id'])) {
                continue;
            }

            $client
                ->query(
                    (new Query(
                        '/ip/hotspot/cookie/remove'
                    ))
                        ->equal(
                            '.id',
                            $cookie['.id']
                        )
                )
                ->read();
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE HOTSPOT USER
        |--------------------------------------------------------------------------
        */

        $users = $client
            ->query(
                (new Query(
                    '/ip/hotspot/user/print'
                ))
                    ->where(
                        'name',
                        $username
                    )
            )
            ->read();

        foreach ($users as $user) {
            if (!isset($user['.id'])) {
                continue;
            }

            $client
                ->query(
                    (new Query(
                        '/ip/hotspot/user/remove'
                    ))
                        ->equal(
                            '.id',
                            $user['.id']
                        )
                )
                ->read();
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE EXPIRY SCHEDULER
        |--------------------------------------------------------------------------
        */

        $schedulerName =
            'expire-' . $username;

        $schedulers = $client
            ->query(
                (new Query(
                    '/system/scheduler/print'
                ))
                    ->where(
                        'name',
                        $schedulerName
                    )
            )
            ->read();

        foreach ($schedulers as $scheduler) {
            if (!isset($scheduler['.id'])) {
                continue;
            }

            $client
                ->query(
                    (new Query(
                        '/system/scheduler/remove'
                    ))
                        ->equal(
                            '.id',
                            $scheduler['.id']
                        )
                )
                ->read();
        }
    }

    /**
     * Create MikroTik API client.
     */
    private function routerClient(
        NetworkRouter $router
    ): Client {
        return new Client([
            'host' => $router->host,

            'user' => $router->username,

            'pass' => Crypt::decryptString(
                $router->password
            ),

            'port' =>
                (int) $router->api_port,

            'ssl' =>
                (bool) $router->use_ssl,

            'timeout' => 5,
        ]);
    }

    /**
     * Calculate voucher expiry time.
     */
    private function calculateExpiry(
        $start,
        int $value,
        string $unit
    ) {
        return match ($unit) {
            'minutes' =>
                $start->addMinutes(
                    $value
                ),

            'hours' =>
                $start->addHours(
                    $value
                ),

            'days' =>
                $start->addDays(
                    $value
                ),

            'weeks' =>
                $start->addWeeks(
                    $value
                ),

            'months' =>
                $start->addMonths(
                    $value
                ),

            default =>
                $start,
        };
    }
}