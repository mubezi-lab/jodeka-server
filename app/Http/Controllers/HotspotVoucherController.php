<?php

namespace App\Http\Controllers;

use App\Models\HotspotProfile;
use App\Models\HotspotVoucher;
use App\Models\NetworkRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use RouterOS\Client;
use RouterOS\Query;

class HotspotVoucherController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | VOUCHER LIST
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $tab = $request->query('tab', 'online');

        if (! in_array($tab, [
            'online',
            'offline',
            'unused',
            'expired',
            'cancelled',
            'all',
        ], true)) {
            $tab = 'online';
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD JODEKA VOUCHERS
        |--------------------------------------------------------------------------
        */

        $allVouchers = HotspotVoucher::with([
            'router',
            'profile',
            'generator',
        ])
            ->where('source', 'jodeka')
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | GET CURRENT ACTIVE USERS FROM MIKROTIK
        |--------------------------------------------------------------------------
        */

        $activeUsers = [];
        $routerErrors = [];

        $routers = NetworkRouter::where(
            'enabled',
            true
        )->get();

        foreach ($routers as $router) {
            try {
                $client = $this->routerClient($router);

                $routerActiveUsers = $client
                    ->query(
                        new Query(
                            '/ip/hotspot/active/print'
                        )
                    )
                    ->read();

                foreach ($routerActiveUsers as $activeUser) {
                    $username =
                        $activeUser['user']
                        ?? null;

                    if (! $username) {
                        continue;
                    }

                    $key =
                        $router->id
                        . '|'
                        . strtolower($username);

                    $activeUsers[$key] = [
                        'router_id' =>
                            $router->id,

                        'username' =>
                            $username,

                        'ip' =>
                            $activeUser['address']
                            ?? null,

                        'mac' =>
                            $activeUser['mac-address']
                            ?? null,

                        'uptime' =>
                            $activeUser['uptime']
                            ?? null,

                        'bytes_in' =>
                            (int) (
                                $activeUser['bytes-in']
                                ?? 0
                            ),

                        'bytes_out' =>
                            (int) (
                                $activeUser['bytes-out']
                                ?? 0
                            ),

                        'packets_in' =>
                            (int) (
                                $activeUser['packets-in']
                                ?? 0
                            ),

                        'packets_out' =>
                            (int) (
                                $activeUser['packets-out']
                                ?? 0
                            ),
                    ];
                }

            } catch (\Throwable $e) {
                $routerErrors[] =
                    ($router->name ?? 'Router')
                    . ': '
                    . $e->getMessage();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ATTACH LIVE DATA + SAVE TO DATABASE
        |--------------------------------------------------------------------------
        */

        $allVouchers->each(
            function ($voucher) use ($activeUsers) {

                $key =
                    $voucher->network_router_id
                    . '|'
                    . strtolower(
                        $voucher->username
                    );

                $active =
                    $activeUsers[$key]
                    ?? null;

                $storedBytes =
                    (int) (
                        $voucher->bytes_in
                        ?? 0
                    )
                    +
                    (int) (
                        $voucher->bytes_out
                        ?? 0
                    );

                /*
                |--------------------------------------------------------------------------
                | CURRENT EXPIRY STATE
                |--------------------------------------------------------------------------
                */

                $expiredByTime =
                    $voucher->expires_at
                    &&
                    $voucher->expires_at->lte(now());

                $voucher->setAttribute(
                    'is_expired_now',
                    $voucher->status === 'expired'
                    || $expiredByTime
                );

                /*
                |--------------------------------------------------------------------------
                | NOT CURRENTLY ONLINE
                |--------------------------------------------------------------------------
                */

                if (! $active) {
                    $voucher->setAttribute(
                        'is_online',
                        false
                    );

                    $voucher->setAttribute(
                        'online_ip',
                        null
                    );

                    $voucher->setAttribute(
                        'online_mac',
                        null
                    );

                    $voucher->setAttribute(
                        'online_uptime',
                        null
                    );

                    $voucher->setAttribute(
                        'display_bytes',
                        $storedBytes
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | TERMINAL / EXPIRED STATUS MUST NOT SHOW ONLINE
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
                    ||
                    $expiredByTime
                ) {
                    $voucher->setAttribute(
                        'is_online',
                        false
                    );

                    $voucher->setAttribute(
                        'display_bytes',
                        $storedBytes
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | FIRST LOGIN
                |--------------------------------------------------------------------------
                */

                if (! $voucher->first_login_at) {
                    $voucher->first_login_at =
                        now();

                    $voucher->used_at =
                        $voucher->first_login_at;

                    /*
                    |--------------------------------------------------------------------------
                    | CALCULATE EXPIRY FROM PROFILE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $voucher->profile
                        &&
                        $voucher->profile->validity_value
                        &&
                        $voucher->profile->validity_unit
                    ) {
                        $voucher->expires_at =
                            $this->calculateExpiry(
                                $voucher
                                    ->first_login_at
                                    ->copy(),

                                (int)
                                    $voucher
                                        ->profile
                                        ->validity_value,

                                $voucher
                                    ->profile
                                    ->validity_unit
                            );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | USED AT
                |--------------------------------------------------------------------------
                */

                if (! $voucher->used_at) {
                    $voucher->used_at =
                        $voucher->first_login_at
                        ?? now();
                }

                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                $voucher->status = 'used';

                /*
                |--------------------------------------------------------------------------
                | DEVICE INFORMATION
                |--------------------------------------------------------------------------
                */

                $voucher->last_seen_at =
                    now();

                $voucher->used_by_ip =
                    $active['ip']
                    ?? $voucher->used_by_ip;

                $voucher->used_by_mac =
                    $active['mac']
                    ?? $voucher->used_by_mac;

                $voucher->mikrotik_uptime =
                    $active['uptime']
                    ?? $voucher->mikrotik_uptime;

                /*
                |--------------------------------------------------------------------------
                | USAGE
                |--------------------------------------------------------------------------
                */

                $voucher->bytes_in =
                    max(
                        (int) (
                            $voucher->bytes_in
                            ?? 0
                        ),

                        (int)
                            $active['bytes_in']
                    );

                $voucher->bytes_out =
                    max(
                        (int) (
                            $voucher->bytes_out
                            ?? 0
                        ),

                        (int)
                            $active['bytes_out']
                    );

                $voucher->packets_in =
                    max(
                        (int) (
                            $voucher->packets_in
                            ?? 0
                        ),

                        (int)
                            $active['packets_in']
                    );

                $voucher->packets_out =
                    max(
                        (int) (
                            $voucher->packets_out
                            ?? 0
                        ),

                        (int)
                            $active['packets_out']
                    );

                $voucher->last_synced_at =
                    now();

                /*
                |--------------------------------------------------------------------------
                | SAVE
                |--------------------------------------------------------------------------
                */

                $voucher->save();

                /*
                |--------------------------------------------------------------------------
                | LIVE UI ATTRIBUTES
                |--------------------------------------------------------------------------
                */

                $activeBytes =
                    (int)
                        $active['bytes_in']
                    +
                    (int)
                        $active['bytes_out'];

                $savedBytes =
                    (int) (
                        $voucher->bytes_in
                        ?? 0
                    )
                    +
                    (int) (
                        $voucher->bytes_out
                        ?? 0
                    );

                $voucher->setAttribute(
                    'is_online',
                    true
                );

                $voucher->setAttribute(
                    'online_ip',
                    $active['ip']
                );

                $voucher->setAttribute(
                    'online_mac',
                    $active['mac']
                );

                $voucher->setAttribute(
                    'online_uptime',
                    $active['uptime']
                );

                $voucher->setAttribute(
                    'display_bytes',
                    max(
                        $storedBytes,
                        $activeBytes,
                        $savedBytes
                    )
                );

                $voucher->setAttribute(
                    'is_expired_now',
                    false
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | COUNTS
        |--------------------------------------------------------------------------
        */

        $onlineCount =
            $allVouchers
                ->where(
                    'is_online',
                    true
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | USED / OFFLINE
        |--------------------------------------------------------------------------
        */

        $offlineCount =
            $allVouchers
                ->filter(function ($voucher) {
                    return
                        $voucher->status === 'used'
                        &&
                        ! $voucher->is_online
                        &&
                        ! $voucher->is_expired_now;
                })
                ->count();

        /*
        |--------------------------------------------------------------------------
        | UNUSED
        |--------------------------------------------------------------------------
        */

        $unusedCount =
            $allVouchers
                ->filter(function ($voucher) {
                    return
                        $voucher->status === 'unused'
                        &&
                        ! $voucher->is_expired_now;
                })
                ->count();

        /*
        |--------------------------------------------------------------------------
        | EXPIRED
        |--------------------------------------------------------------------------
        */

        $expiredCount =
            $allVouchers
                ->where(
                    'is_expired_now',
                    true
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | CANCELLED
        |--------------------------------------------------------------------------
        */

        $cancelledCount =
            $allVouchers
                ->where(
                    'status',
                    'cancelled'
                )
                ->count();

        /*
        |--------------------------------------------------------------------------
        | ALL
        |--------------------------------------------------------------------------
        */

        $allCount =
            $allVouchers->count();

        /*
        |--------------------------------------------------------------------------
        | FILTER TAB
        |--------------------------------------------------------------------------
        */

        if ($tab === 'online') {

            $vouchers =
                $allVouchers
                    ->where(
                        'is_online',
                        true
                    )
                    ->sortByDesc(
                        'display_bytes'
                    )
                    ->values();

        } elseif ($tab === 'offline') {

            $vouchers =
                $allVouchers
                    ->filter(function ($voucher) {
                        return
                            $voucher->status === 'used'
                            &&
                            ! $voucher->is_online
                            &&
                            ! $voucher->is_expired_now;
                    })
                    ->sortByDesc(
                        function ($voucher) {
                            return optional(
                                $voucher->last_seen_at
                            )->timestamp ?? 0;
                        }
                    )
                    ->values();

        } elseif ($tab === 'unused') {

            $vouchers =
                $allVouchers
                    ->filter(function ($voucher) {
                        return
                            $voucher->status === 'unused'
                            &&
                            ! $voucher->is_expired_now;
                    })
                    ->sortByDesc(
                        function ($voucher) {
                            return optional(
                                $voucher->generated_at
                                ?? $voucher->created_at
                            )->timestamp ?? 0;
                        }
                    )
                    ->values();

        } elseif ($tab === 'expired') {

            $vouchers =
                $allVouchers
                    ->where(
                        'is_expired_now',
                        true
                    )
                    ->sortByDesc(
                        function ($voucher) {
                            return optional(
                                $voucher->expires_at
                            )->timestamp ?? 0;
                        }
                    )
                    ->values();

        } elseif ($tab === 'cancelled') {

            $vouchers =
                $allVouchers
                    ->where(
                        'status',
                        'cancelled'
                    )
                    ->sortByDesc(
                        function ($voucher) {
                            return optional(
                                $voucher->disabled_at
                            )->timestamp ?? 0;
                        }
                    )
                    ->values();

        } else {

            $vouchers =
                $allVouchers
                    ->sortByDesc(
                        function ($voucher) {
                            return optional(
                                $voucher->created_at
                            )->timestamp ?? 0;
                        }
                    )
                    ->values();
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'network.vouchers.index',
            [
                'vouchers' =>
                    $vouchers,

                'tab' =>
                    $tab,

                'onlineCount' =>
                    $onlineCount,

                'offlineCount' =>
                    $offlineCount,

                'unusedCount' =>
                    $unusedCount,

                'expiredCount' =>
                    $expiredCount,

                'cancelledCount' =>
                    $cancelledCount,

                'allCount' =>
                    $allCount,

                'routerErrors' =>
                    $routerErrors,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MIKROTIK VOUCHERS
    |--------------------------------------------------------------------------
    */

    public function mikrotik()
    {
        $vouchers =
            HotspotVoucher::with([
                'router',
                'profile',
                'generator',
            ])
                ->where(
                    'source',
                    'mikrotik'
                )
                ->latest()
                ->get();

        return view(
            'network.vouchers.mikrotik',
            compact('vouchers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VOUCHER DETAILS
    |--------------------------------------------------------------------------
    */

    public function show(
        HotspotVoucher $hotspotVoucher
    ) {
        $hotspotVoucher->load([
            'profile',
            'router',
            'generator',
        ]);

        return view(
            'network.vouchers.show',
            [
                'voucher' =>
                    $hotspotVoucher,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE PAGE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $profiles =
            HotspotProfile::with(
                'router'
            )
                ->where(
                    'enabled',
                    true
                )
                ->orderBy(
                    'price'
                )
                ->get();

        return view(
            'network.vouchers.create',
            compact('profiles')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE VOUCHER
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {
        $data =
            $request->validate([
                'hotspot_profile_id' =>
                    'required|exists:hotspot_profiles,id',

                'comment' =>
                    'nullable|string|max:255',
            ]);

        $profile =
            HotspotProfile::with(
                'router'
            )
                ->findOrFail(
                    $data[
                        'hotspot_profile_id'
                    ]
                );

        if (! $profile->enabled) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Selected hotspot profile is disabled.'
                );
        }

        $router =
            $profile->router;

        if (
            ! $router
            ||
            ! $router->enabled
        ) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Router is not available or is disabled.'
                );
        }

        try {
            $username =
                $this->generateVoucherCode(
                    $profile->voucher_prefix
                    ?: 'JDK'
                );

            $password =
                $username;

            $client =
                $this->routerClient(
                    $router
                );

            $query =
                (new Query(
                    '/ip/hotspot/user/add'
                ))
                    ->equal(
                        'name',
                        $username
                    )
                    ->equal(
                        'password',
                        $password
                    )
                    ->equal(
                        'profile',
                        $profile->mikrotik_profile
                    );

            if (
                ! empty(
                    $data['comment']
                )
            ) {
                $query->equal(
                    'comment',
                    $data['comment']
                );
            }

            $client
                ->query($query)
                ->read();

            HotspotVoucher::create([
                'network_router_id' =>
                    $router->id,

                'hotspot_profile_id' =>
                    $profile->id,

                'username' =>
                    $username,

                'password' =>
                    $password,

                'price' =>
                    $profile->price,

                'status' =>
                    'unused',

                'generated_at' =>
                    now(),

                'generated_by' =>
                    auth()->id(),

                'comment' =>
                    $data['comment']
                    ?? null,
            ]);

            return redirect()
                ->route(
                    'hotspot-vouchers.index'
                )
                ->with(
                    'success',
                    'Voucher generated successfully: '
                    . $username
                );

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Voucher generation failed: '
                    . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SYNC STATUS
    |--------------------------------------------------------------------------
    */

    public function syncStatus()
    {
        $routers =
            NetworkRouter::where(
                'enabled',
                true
            )->get();

        $updated = 0;
        $expired = 0;
        $deletedFromMikrotik = 0;

        try {
            foreach ($routers as $router) {

                $client =
                    $this->routerClient(
                        $router
                    );

                /*
                |--------------------------------------------------------------------------
                | ACTIVE USERS
                |--------------------------------------------------------------------------
                */

                $activeUsers =
                    $client
                        ->query(
                            new Query(
                                '/ip/hotspot/active/print'
                            )
                        )
                        ->read();

                foreach (
                    $activeUsers
                    as $activeUser
                ) {
                    $username =
                        $activeUser['user']
                        ?? null;

                    if (! $username) {
                        continue;
                    }

                    $voucher =
                        HotspotVoucher::with(
                            'profile'
                        )
                            ->where(
                                'network_router_id',
                                $router->id
                            )
                            ->where(
                                'username',
                                $username
                            )
                            ->first();

                    if (! $voucher) {
                        continue;
                    }

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
                    | FIRST LOGIN
                    |--------------------------------------------------------------------------
                    */

                    if (! $voucher->first_login_at) {
                        $voucher->first_login_at =
                            now();

                        $voucher->used_at =
                            $voucher->first_login_at;

                        if (
                            $voucher->profile
                            &&
                            $voucher
                                ->profile
                                ->validity_value
                            &&
                            $voucher
                                ->profile
                                ->validity_unit
                        ) {
                            $voucher->expires_at =
                                $this->calculateExpiry(
                                    $voucher
                                        ->first_login_at
                                        ->copy(),

                                    (int)
                                        $voucher
                                            ->profile
                                            ->validity_value,

                                    $voucher
                                        ->profile
                                        ->validity_unit
                                );
                        }
                    }

                    if (! $voucher->used_at) {
                        $voucher->used_at =
                            $voucher->first_login_at
                            ?? now();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    $voucher->status =
                        'used';

                    $voucher->last_seen_at =
                        now();

                    /*
                    |--------------------------------------------------------------------------
                    | DEVICE
                    |--------------------------------------------------------------------------
                    */

                    $voucher->used_by_ip =
                        $activeUser['address']
                        ?? $voucher->used_by_ip;

                    $voucher->used_by_mac =
                        $activeUser['mac-address']
                        ?? $voucher->used_by_mac;

                    $voucher->mikrotik_uptime =
                        $activeUser['uptime']
                        ?? $voucher->mikrotik_uptime;

                    /*
                    |--------------------------------------------------------------------------
                    | BYTES
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isset(
                            $activeUser['bytes-in']
                        )
                    ) {
                        $voucher->bytes_in =
                            max(
                                (int) (
                                    $voucher->bytes_in
                                    ?? 0
                                ),

                                (int)
                                    $activeUser['bytes-in']
                            );
                    }

                    if (
                        isset(
                            $activeUser['bytes-out']
                        )
                    ) {
                        $voucher->bytes_out =
                            max(
                                (int) (
                                    $voucher->bytes_out
                                    ?? 0
                                ),

                                (int)
                                    $activeUser['bytes-out']
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PACKETS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isset(
                            $activeUser['packets-in']
                        )
                    ) {
                        $voucher->packets_in =
                            max(
                                (int) (
                                    $voucher->packets_in
                                    ?? 0
                                ),

                                (int)
                                    $activeUser['packets-in']
                            );
                    }

                    if (
                        isset(
                            $activeUser['packets-out']
                        )
                    ) {
                        $voucher->packets_out =
                            max(
                                (int) (
                                    $voucher->packets_out
                                    ?? 0
                                ),

                                (int)
                                    $activeUser['packets-out']
                            );
                    }

                    $voucher->last_synced_at =
                        now();

                    $voucher->save();

                    $updated++;
                }

                /*
                |--------------------------------------------------------------------------
                | FIND EXPIRED VOUCHERS
                |--------------------------------------------------------------------------
                */

                $expiredVouchers =
                    HotspotVoucher::where(
                        'network_router_id',
                        $router->id
                    )
                        ->where(
                            'status',
                            'used'
                        )
                        ->whereNotNull(
                            'expires_at'
                        )
                        ->where(
                            'expires_at',
                            '<=',
                            now()
                        )
                        ->get();

                foreach (
                    $expiredVouchers
                    as $voucher
                ) {
                    $this
                        ->removeVoucherFromMikrotik(
                            $client,
                            $voucher->username
                        );

                    $voucher->status =
                        'expired';

                    $voucher->disabled_at =
                        now();

                    $voucher->last_synced_at =
                        now();

                    $voucher->save();

                    $expired++;

                    $deletedFromMikrotik++;
                }
            }

            return redirect()
                ->route(
                    'hotspot-vouchers.index'
                )
                ->with(
                    'success',
                    'Sync completed. Updated: '
                    . $updated
                    . ', Expired: '
                    . $expired
                    . ', Deleted from MikroTik: '
                    . $deletedFromMikrotik
                );

        } catch (\Throwable $e) {
            return redirect()
                ->route(
                    'hotspot-vouchers.index'
                )
                ->with(
                    'error',
                    'Voucher status sync failed: '
                    . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CANCEL VOUCHER
    |--------------------------------------------------------------------------
    */

    public function cancel(
        HotspotVoucher $hotspotVoucher
    ) {
        if (
            in_array(
                $hotspotVoucher->status,
                [
                    'expired',
                    'cancelled',
                    'disabled',
                ],
                true
            )
        ) {
            return back()
                ->with(
                    'error',
                    'This voucher cannot be cancelled because its current status is '
                    . $hotspotVoucher->status
                    . '.'
                );
        }

        $router =
            $hotspotVoucher->router;

        if (
            ! $router
            ||
            ! $router->enabled
        ) {
            return back()
                ->with(
                    'error',
                    'Router is not available or is disabled.'
                );
        }

        try {
            $client =
                $this->routerClient(
                    $router
                );

            $this
                ->removeVoucherFromMikrotik(
                    $client,
                    $hotspotVoucher->username
                );

            $hotspotVoucher->status =
                'cancelled';

            $hotspotVoucher->disabled_at =
                now();

            $hotspotVoucher->last_synced_at =
                now();

            $hotspotVoucher->save();

            return redirect()
                ->route(
                    'hotspot-vouchers.index'
                )
                ->with(
                    'success',
                    'Voucher cancelled successfully: '
                    . $hotspotVoucher->username
                );

        } catch (\Throwable $e) {
            return redirect()
                ->route(
                    'hotspot-vouchers.index'
                )
                ->with(
                    'error',
                    'Voucher cancellation failed: '
                    . $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | REMOVE VOUCHER FROM MIKROTIK
    |--------------------------------------------------------------------------
    */

    private function removeVoucherFromMikrotik(
        Client $client,
        string $username
    ): void {
        /*
        |--------------------------------------------------------------------------
        | ACTIVE SESSION
        |--------------------------------------------------------------------------
        */

        $activeSessions =
            $client
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
            if (! isset($session['.id'])) {
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
        | COOKIE
        |--------------------------------------------------------------------------
        */

        $cookies =
            $client
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
            if (! isset($cookie['.id'])) {
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
        | HOTSPOT USER
        |--------------------------------------------------------------------------
        */

        $users =
            $client
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
            if (! isset($user['.id'])) {
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
        | EXPIRY SCHEDULER
        |--------------------------------------------------------------------------
        */

        $schedulerName =
            'expire-'
            . $username;

        $schedulers =
            $client
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
            if (! isset($scheduler['.id'])) {
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

    /*
    |--------------------------------------------------------------------------
    | ROUTER CLIENT
    |--------------------------------------------------------------------------
    */

    private function routerClient(
        NetworkRouter $router
    ): Client {
        return new Client([
            'host' =>
                $router->host,

            'user' =>
                $router->username,

            'pass' =>
                Crypt::decryptString(
                    $router->password
                ),

            'port' =>
                (int)
                    $router->api_port,

            'ssl' =>
                (bool)
                    $router->use_ssl,

            'timeout' =>
                5,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE UNIQUE VOUCHER
    |--------------------------------------------------------------------------
    */

    private function generateVoucherCode(
        string $prefix
    ): string {
        do {
            $code =
                strtoupper(
                    $prefix
                )
                . random_int(
                    10000,
                    99999
                );

        } while (
            HotspotVoucher::where(
                'username',
                $code
            )->exists()
        );

        return $code;
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE EXPIRY
    |--------------------------------------------------------------------------
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