<?php

namespace App\Http\Controllers;

use App\Models\HotspotProfile;
use Illuminate\Http\Request;

class HotspotProfileController extends Controller
{
    public function index()
    {
        $profiles = HotspotProfile::with('router')
            ->orderBy('name')
            ->get();

        return view(
            'network.profiles.index',
            compact('profiles')
        );
    }

    public function edit(HotspotProfile $hotspotProfile)
{
    return view(
        'network.profiles.edit',
        compact('hotspotProfile')
    );
}

    public function update(Request $request, HotspotProfile $hotspotProfile)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'validity_value' => 'required|integer|min:1',
            'validity_unit' => 'required|in:minutes,hours,days,weeks,months',
            'voucher_prefix' => 'required|string|max:20',
            'enabled' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $data['enabled'] = $request->boolean('enabled');

        $hotspotProfile->update($data);

        return redirect()
            ->route('hotspot-profiles.index')
            ->with('success', 'Profile updated successfully.');
    }
}
