<?php

namespace App\Http\Controllers;

use App\Services\HotspotCustomerInvitationService;
use Illuminate\Http\RedirectResponse;

class HotspotCustomerInvitationController extends Controller
{
    public function store(
        HotspotCustomerInvitationService $service
    ): RedirectResponse {
        $queued = $service->queueEligible();

        if ($queued === 0) {
            return redirect()
                ->route('hotspot-vouchers.index')
                ->with(
                    'error',
                    'Hakuna wateja wapya wanaostahili kutumiwa mwaliko leo.'
                );
        }

        return redirect()
            ->route('hotspot-vouchers.index')
            ->with(
                'success',
                'Mialiko ya Hotspot imewekwa kwenye foleni kwa wateja '
                . $queued
                . '.'
            );
    }
}
