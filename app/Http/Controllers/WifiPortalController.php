<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WifiPortalController extends Controller
{
    /**
     * Display the public JODEKA Wi-Fi captive portal.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | MikroTik Hotspot information
        |--------------------------------------------------------------------------
        |
        | These values are sent by MikroTik when an unauthenticated client
        | is redirected from the captive portal to JODEKA.
        |
        */

        $loginUrl = $request->query('login');
        $originalUrl = $request->query('dst');
        $mac = $request->query('mac');
        $ip = $request->query('ip');

        return view('wifi.portal', [
            'loginUrl' => $loginUrl,
            'originalUrl' => $originalUrl,
            'mac' => $mac,
            'ip' => $ip,
        ]);
    }
}