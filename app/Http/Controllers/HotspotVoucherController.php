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
    public function index()
    {
        $vouchers = HotspotVoucher::with([
            'router',
            'profile',
            'generator',
        ])
            ->where('source', 'jodeka')
            ->latest()
            ->get();

        return view(
            'network.vouchers.index',
            compact('vouchers')
        );
    }

    public function mikrotik()
    {
        $vouchers = HotspotVoucher::with([
            'router',
            'profile',
            'generator',
        ])
            ->where('source', 'mikrotik')
            ->latest()
            ->get();

        return view(
            'network.vouchers.mikrotik',
            compact('vouchers')
        );
    }

    public function show(\App\Models\HotspotVoucher $hotspotVoucher)
    {
        $hotspotVoucher->load([
            'profile',
            'router',
            'generator',
        ]);

        return view('network.vouchers.show', [
            'voucher' => $hotspotVoucher,
        ]);
    }

    public function create()
    {
        $profiles = HotspotProfile::with('router')
            ->where('enabled', true)
            ->orderBy('price')
            ->get();

        return view(
            'network.vouchers.create',
            compact('profiles')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hotspot_profile_id' => 'required|exists:hotspot_profiles,id',
            'comment' => 'nullable|string|max:255',
        ]);

        $profile = HotspotProfile::with('router')
            ->findOrFail($data['hotspot_profile_id']);

        if (!$profile->enabled) {
            return back()
                ->withInput()
                ->with('error', 'Selected hotspot profile is disabled.');
        }

        $router = $profile->router;

        if (!$router || !$router->enabled) {
            return back()
                ->withInput()
                ->with('error', 'Router is not available or is disabled.');
        }

        try {
            $username = $this->generateVoucherCode(
                $profile->voucher_prefix ?: 'JDK'
            );

            $password = $username;

            $client = $this->routerClient($router);

            $query = (new Query('/ip/hotspot/user/add'))
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('profile', $profile->mikrotik_profile);

            if (!empty($data['comment'])) {
                $query->equal('comment', $data['comment']);
            }

            $client->query($query)->read();

            HotspotVoucher::create([
                'network_router_id' => $router->id,
                'hotspot_profile_id' => $profile->id,
                'username' => $username,
                'password' => $password,
                'price' => $profile->price,
                'status' => 'unused',
                'generated_at' => now(),
                'generated_by' => auth()->id(),
                'comment' => $data['comment'] ?? null,
            ]);

            return redirect()
                ->route('hotspot-vouchers.index')
                ->with(
                    'success',
                    'Voucher generated successfully: ' . $username
                );

        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Voucher generation failed: ' . $e->getMessage()
                );
        }
    }

    public function syncStatus()
    {
        $routers = NetworkRouter::where('enabled', true)->get();

        $updated = 0;
        $expired = 0;
        $deletedFromMikrotik = 0;

        try {
            foreach ($routers as $router) {

                $client = $this->routerClient($router);

                $activeUsers = $client
                    ->query(
                        new Query('/ip/hotspot/active/print')
                    )
                    ->read();

                foreach ($activeUsers as $activeUser) {

                    $username = $activeUser['user'] ?? null;

                    if (!$username) {
                        continue;
                    }

                    $voucher = HotspotVoucher::with('profile')
                        ->where('network_router_id', $router->id)
                        ->where('username', $username)
                        ->first();

                    if (!$voucher) {
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

                    if (!$voucher->first_login_at) {

                        $voucher->first_login_at = now();
                        $voucher->used_at = $voucher->first_login_at;

                        if ($voucher->profile) {

                            $voucher->expires_at =
                                $this->calculateExpiry(
                                    $voucher->first_login_at->copy(),
                                    (int) $voucher->profile->validity_value,
                                    $voucher->profile->validity_unit
                                );
                        }
                    }

                    if (!$voucher->used_at) {
                        $voucher->used_at =
                            $voucher->first_login_at ?? now();
                    }

                    $voucher->status = 'used';
                    $voucher->last_seen_at = now();

                    $voucher->used_by_ip =
                        $activeUser['address']
                        ?? $voucher->used_by_ip;

                    $voucher->used_by_mac =
                        $activeUser['mac-address']
                        ?? $voucher->used_by_mac;

                    $voucher->save();

                    $updated++;
                }

                $expiredVouchers = HotspotVoucher::where(
                        'network_router_id',
                        $router->id
                    )
                    ->where('status', 'used')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now())
                    ->get();

                foreach ($expiredVouchers as $voucher) {

                    $this->removeVoucherFromMikrotik(
                        $client,
                        $voucher->username
                    );

                    $voucher->status = 'expired';
                    $voucher->disabled_at = now();
                    $voucher->save();

                    $expired++;
                    $deletedFromMikrotik++;
                }
            }

            return redirect()
                ->route('hotspot-vouchers.index')
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
                ->route('hotspot-vouchers.index')
                ->with(
                    'error',
                    'Voucher status sync failed: '
                    . $e->getMessage()
                );
        }
    }

    public function cancel(HotspotVoucher $hotspotVoucher)
    {
        if (
            in_array(
                $hotspotVoucher->status,
                ['expired', 'cancelled', 'disabled'],
                true
            )
        ) {
            return back()->with(
                'error',
                'This voucher cannot be cancelled because its current status is '
                . $hotspotVoucher->status
                . '.'
            );
        }

        $router = $hotspotVoucher->router;

        if (!$router || !$router->enabled) {
            return back()->with(
                'error',
                'Router is not available or is disabled.'
            );
        }

        try {
            $client = $this->routerClient($router);

            $this->removeVoucherFromMikrotik(
                $client,
                $hotspotVoucher->username
            );

            $hotspotVoucher->status = 'cancelled';
            $hotspotVoucher->disabled_at = now();
            $hotspotVoucher->save();

            return redirect()
                ->route('hotspot-vouchers.index')
                ->with(
                    'success',
                    'Voucher cancelled successfully: '
                    . $hotspotVoucher->username
                );

        } catch (\Throwable $e) {

            return redirect()
                ->route('hotspot-vouchers.index')
                ->with(
                    'error',
                    'Voucher cancellation failed: '
                    . $e->getMessage()
                );
        }
    }

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
                (new Query('/ip/hotspot/active/print'))
                    ->where('user', $username)
            )
            ->read();

        foreach ($activeSessions as $session) {

            if (!isset($session['.id'])) {
                continue;
            }

            $client
                ->query(
                    (new Query('/ip/hotspot/active/remove'))
                        ->equal('.id', $session['.id'])
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
                (new Query('/ip/hotspot/cookie/print'))
                    ->where('user', $username)
            )
            ->read();

        foreach ($cookies as $cookie) {

            if (!isset($cookie['.id'])) {
                continue;
            }

            $client
                ->query(
                    (new Query('/ip/hotspot/cookie/remove'))
                        ->equal('.id', $cookie['.id'])
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
                (new Query('/ip/hotspot/user/print'))
                    ->where('name', $username)
            )
            ->read();

        foreach ($users as $user) {

            if (!isset($user['.id'])) {
                continue;
            }

            $client
                ->query(
                    (new Query('/ip/hotspot/user/remove'))
                        ->equal('.id', $user['.id'])
                )
                ->read();
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE EXPIRY SCHEDULER
        |--------------------------------------------------------------------------
        */

        $schedulerName = 'expire-' . $username;

        $schedulers = $client
            ->query(
                (new Query('/system/scheduler/print'))
                    ->where('name', $schedulerName)
            )
            ->read();

        foreach ($schedulers as $scheduler) {

            if (!isset($scheduler['.id'])) {
                continue;
            }

            $client
                ->query(
                    (new Query('/system/scheduler/remove'))
                        ->equal('.id', $scheduler['.id'])
                )
                ->read();
        }
    }

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

    private function generateVoucherCode(
        string $prefix
    ): string {
        do {
            $code =
                strtoupper($prefix)
                . random_int(10000, 99999);

        } while (
            HotspotVoucher::where(
                'username',
                $code
            )->exists()
        );

        return $code;
    }

    private function calculateExpiry(
        $start,
        int $value,
        string $unit
    ) {
        return match ($unit) {
            'minutes' => $start->addMinutes($value),
            'hours'   => $start->addHours($value),
            'days'    => $start->addDays($value),
            'weeks'   => $start->addWeeks($value),
            'months'  => $start->addMonths($value),
            default   => $start,
        };
    }
}