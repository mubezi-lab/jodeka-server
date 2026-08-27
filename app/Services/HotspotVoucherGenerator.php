<?php

namespace App\Services;

use App\Models\HotspotProfile;
use App\Models\HotspotVoucher;
use Illuminate\Support\Facades\Crypt;
use RouterOS\Client;
use RouterOS\Query;
use RuntimeException;
use Throwable;

class HotspotVoucherGenerator
{
    public function generate(
        HotspotProfile $profile,
        ?string $comment = null,
        ?int $generatedBy = null
    ): HotspotVoucher {
        $profile->loadMissing('router');

        if (!$profile->enabled) {
            throw new RuntimeException(
                'Selected hotspot profile is disabled.'
            );
        }

        $router = $profile->router;

        if (!$router || !$router->enabled) {
            throw new RuntimeException(
                'Router is not available or is disabled.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Voucher Code
        |--------------------------------------------------------------------------
        */

        $username = $this->generateVoucherCode(
            $profile->voucher_prefix ?: 'JDK'
        );

        $password = $username;

        /*
        |--------------------------------------------------------------------------
        | Connect to MikroTik
        |--------------------------------------------------------------------------
        */

        try {
            $client = new Client([
                'host' => $router->host,
                'user' => $router->username,
                'pass' => Crypt::decryptString($router->password),
                'port' => (int) $router->api_port,
                'ssl' => (bool) $router->use_ssl,
                'timeout' => 5,
            ]);
        } catch (Throwable $e) {
            throw new RuntimeException(
                'MikroTik connection failed: ' . $e->getMessage(),
                0,
                $e
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Create MikroTik Hotspot User
        |--------------------------------------------------------------------------
        */

        try {
            $query = (new Query('/ip/hotspot/user/add'))
                ->equal('name', $username)
                ->equal('password', $password)
                ->equal('profile', $profile->mikrotik_profile);

            if (!empty($comment)) {
                $query->equal('comment', $comment);
            }

            $response = $client->query($query)->read();

            if (isset($response['after']['message'])) {
                throw new RuntimeException(
                    'MikroTik error: '
                    . $response['after']['message']
                );
            }

        } catch (Throwable $e) {
            throw new RuntimeException(
                'MikroTik hotspot user creation failed: '
                . $e->getMessage(),
                0,
                $e
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Save Voucher in JODEKA
        |--------------------------------------------------------------------------
        */

        try {
            return HotspotVoucher::create([
                'network_router_id' => $router->id,
                'hotspot_profile_id' => $profile->id,
                'username' => $username,
                'password' => $password,
                'price' => $profile->price,
                'status' => 'unused',
                'source' => 'jodeka',
                'generated_at' => now(),
                'generated_by' => $generatedBy,
                'comment' => $comment,
            ]);

        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Cleanup MikroTik User if Database Save Fails
            |--------------------------------------------------------------------------
            */

            try {
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

            } catch (Throwable) {
                // Tunahifadhi original database error.
            }

            throw new RuntimeException(
                'Voucher database save failed: '
                . $e->getMessage(),
                0,
                $e
            );
        }
    }

    private function generateVoucherCode(string $prefix): string
    {
        do {
            $code = strtoupper($prefix)
                . random_int(10000, 99999);

        } while (
            HotspotVoucher::where('username', $code)->exists()
        );

        return $code;
    }
}