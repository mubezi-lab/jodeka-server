<?php

namespace App\Services;

use App\Models\NetworkRouter;
use Illuminate\Support\Facades\Crypt;
use RouterOS\Client;

class MikrotikClientFactory
{
    public function make(NetworkRouter $router): Client
    {
        return new Client([
            'host' => $router->host,
            'user' => $router->username,
            'pass' => Crypt::decryptString($router->password),
            'port' => (int) $router->api_port,
            'ssl' => (bool) $router->use_ssl,
            'timeout' => 10,
        ]);
    }
}
