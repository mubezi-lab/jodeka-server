<?php

namespace App\Http\Controllers;

use App\Models\NetworkRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use RouterOS\Client;
use RouterOS\Query;
use App\Models\HotspotProfile;

class NetworkRouterController extends Controller
{
    public function index()
    {
        $routers = NetworkRouter::latest()->get();

        return view('network.routers.index', compact('routers'));
    }

    public function create()
    {
        return view('network.routers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'api_port' => 'required|integer',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'use_ssl' => 'nullable|boolean',
            'enabled' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['password'] = Crypt::encryptString($data['password']);
        $data['use_ssl'] = $request->boolean('use_ssl');
        $data['enabled'] = $request->boolean('enabled');

        NetworkRouter::create($data);

        return redirect()
            ->route('network-routers.index')
            ->with('success', 'Router added successfully.');
    }

    public function edit(NetworkRouter $networkRouter)
    {
        return view('network.routers.edit', compact('networkRouter'));
    }

    public function update(Request $request, NetworkRouter $networkRouter)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'api_port' => 'required|integer',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'use_ssl' => 'nullable|boolean',
            'enabled' => 'nullable|boolean',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Crypt::encryptString($data['password']);
        } else {
            unset($data['password']);
        }

        $data['use_ssl'] = $request->boolean('use_ssl');
        $data['enabled'] = $request->boolean('enabled');

        $networkRouter->update($data);

        return redirect()
            ->route('network-routers.index')
            ->with('success', 'Router updated successfully.');
    }

    public function destroy(NetworkRouter $networkRouter)
    {
        $networkRouter->delete();

        return redirect()
            ->route('network-routers.index')
            ->with('success', 'Router deleted successfully.');
    }


    public function testConnection(NetworkRouter $networkRouter)
    {
        try {
            $client = new Client([
                'host' => $networkRouter->host,
                'user' => $networkRouter->username,
                'pass' => Crypt::decryptString($networkRouter->password),
                'port' => (int) $networkRouter->api_port,
                'ssl' => (bool) $networkRouter->use_ssl,
                'timeout' => 5,
            ]);

            $identity = $client->query(new Query('/system/identity/print'))->read();

            return back()->with(
                'success',
                'Connected successfully. Router: ' . ($identity[0]['name'] ?? 'Unknown')
            );

        } catch (\Throwable $e) {
            return back()->with(
                'error',
                'Connection failed: ' . $e->getMessage()
            );
        }
    }

    public function syncProfiles(NetworkRouter $networkRouter)
    {
        try {
            $client = new Client([
                'host' => $networkRouter->host,
                'user' => $networkRouter->username,
                'pass' => Crypt::decryptString($networkRouter->password),
                'port' => (int) $networkRouter->api_port,
                'ssl' => (bool) $networkRouter->use_ssl,
                'timeout' => 5,
            ]);

            $profiles = $client
                ->query(new Query('/ip/hotspot/user/profile/print'))
                ->read();

            foreach ($profiles as $profile) {
                HotspotProfile::updateOrCreate(
                    [
                        'network_router_id' => $networkRouter->id,
                        'mikrotik_profile' => $profile['name'],
                    ],
                    [
                        'name' => $profile['name'],
                        'price' => 0,
                        'validity_hours' => 0,
                        'enabled' => true,
                        'description' => 'Synced from MikroTik',
                    ]
                );
            }

            return back()->with(
                'success',
                'Profiles synced successfully: ' . count($profiles)
            );

        } catch (\Throwable $e) {
            return back()->with(
                'error',
                'Sync failed: ' . $e->getMessage()
            );
        }
    }
}