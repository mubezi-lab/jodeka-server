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

        foreach ($routers as $router) {
            try {
                $client = $this->routerClient($router);

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
                    |
                    | These counters remain useful even when the customer
                    | is no longer in /ip/hotspot/active.
                    |
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
                    |
                    | IMPORTANT:
                    | Used does NOT mean currently online.
                    | It means the voucher has been used at least once.
                    |
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
                        | We only create first_login_at when JODEKA actually
                        | sees the voucher active.
                        |
                        */

                        if (!$voucher->first_login_at) {
                            $voucher->first_login_at = now();

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
                        | Prefer the permanent user uptime when available.
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
                                    $finalUser['uptime'] ?? null
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
            . ', Expired: '
            . $expired
            . ', Deleted from MikroTik: '
            . $deletedFromMikrotik
        );

        return self::SUCCESS;
    }

    /**
     * Check whether MikroTik uptime represents actual usage.
     */
    private function uptimeHasUsage(?string $uptime): bool
    {
        if (!$uptime) {
            return false;
        }

        $uptime = trim($uptime);

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

            'port' => (int) $router->api_port,

            'ssl' => (bool) $router->use_ssl,

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
                $start->addMinutes($value),

            'hours' =>
                $start->addHours($value),

            'days' =>
                $start->addDays($value),

            'weeks' =>
                $start->addWeeks($value),

            'months' =>
                $start->addMonths($value),

            default =>
                $start,
        };
    }
}