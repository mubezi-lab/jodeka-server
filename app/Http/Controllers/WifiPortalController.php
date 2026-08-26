<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WifiPortalController extends Controller
{
    public function index(Request $request)
    {
        $loginUrl = $request->query('login');
        $originalUrl = $request->query('dst');
        $mac = $request->query('mac');
        $ip = $request->query('ip');

        /*
        |--------------------------------------------------------------------------
        | DETECT DEVICE TYPE
        |--------------------------------------------------------------------------
        */

        $userAgent = strtolower($request->userAgent() ?? '');

        $deviceType = 'Kompyuta';

        if (
            str_contains($userAgent, 'iphone') ||
            str_contains($userAgent, 'android') ||
            str_contains($userAgent, 'mobile')
        ) {
            $deviceType = 'Simu';
        }

        if (
            str_contains($userAgent, 'ipad') ||
            str_contains($userAgent, 'tablet')
        ) {
            $deviceType = 'Tablet';
        }

        return view('wifi.portal', [
            'loginUrl' => $loginUrl,
            'originalUrl' => $originalUrl,
            'mac' => $mac,
            'ip' => $ip,
            'deviceType' => $deviceType,
        ]);
    }
}