<?php

namespace Tests\Feature;

use App\Http\Controllers\HotspotPermanentUserController;
use App\Models\HotspotPermanentUser;
use App\Models\NetworkRouter;
use App\Services\HotspotPermanentBindingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class HotspotPermanentDeviceChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_change_preserves_user_identity_type_and_history(): void
    {
        $router = $this->insertRouter();

        $user = HotspotPermanentUser::create([
            'network_router_id' => $router->id,
            'name' => 'Fetty Grocery',
            'phone' => '0705450312',
            'normalized_phone' => '255705450312',
            'mac_address' => '4E:D7:8D:F2:EF:A8',
            'user_type' => 'staff',
            'daily_rate' => 0,
            'usage_threshold_bytes' => 1048576,
            'enabled' => false,
            'is_online' => false,
        ]);

        $user->usages()->create([
            'usage_date' => now()->toDateString(),
            'bytes_in' => 100,
            'bytes_out' => 200,
        ]);

        $bindings = Mockery::mock(HotspotPermanentBindingService::class);
        $bindings->shouldReceive('replaceDevice')->once()->with(
            Mockery::on(fn ($value) => $value->is($router)),
            '4E:D7:8D:F2:EF:A8',
            'DE:9A:3A:52:A6:FC',
            'Fetty Grocery',
            'staff'
        );

        $request = Request::create('/hotspot-permanent-users/' . $user->id . '/device', 'PATCH', [
            'mac_address' => 'de:9a:3a:52:a6:fc',
        ]);

        app(HotspotPermanentUserController::class)->changeDevice($request, $user, $bindings);

        $fresh = HotspotPermanentUser::findOrFail($user->id);

        $this->assertSame($user->id, $fresh->id);
        $this->assertSame('DE:9A:3A:52:A6:FC', $fresh->mac_address);
        $this->assertSame('staff', $fresh->user_type);
        $this->assertSame('255705450312', $fresh->normalized_phone);
        $this->assertTrue($fresh->enabled);
        $this->assertSame(1, $fresh->usages()->count());
    }

    public function test_device_change_rejects_mac_used_by_another_user_on_same_router(): void
    {
        $router = $this->insertRouter();

        $first = HotspotPermanentUser::create([
            'network_router_id' => $router->id,
            'name' => 'First',
            'mac_address' => 'AA:AA:AA:AA:AA:AA',
            'user_type' => 'staff',
            'daily_rate' => 0,
        ]);
        $second = HotspotPermanentUser::create([
            'network_router_id' => $router->id,
            'name' => 'Second',
            'mac_address' => 'BB:BB:BB:BB:BB:BB',
            'user_type' => 'staff',
            'daily_rate' => 0,
        ]);

        $bindings = Mockery::mock(HotspotPermanentBindingService::class);
        $bindings->shouldNotReceive('replaceDevice');

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $request = Request::create('/hotspot-permanent-users/' . $first->id . '/device', 'PATCH', [
            'mac_address' => $second->mac_address,
        ]);

        app(HotspotPermanentUserController::class)->changeDevice($request, $first, $bindings);
    }

    private function insertRouter(): NetworkRouter
    {
        DB::table('network_routers')->insert([
            'id' => 999,
            'name' => 'JODEKA Gateway',
            'host' => '192.0.2.1',
            'api_port' => 8728,
            'username' => 'test',
            'password' => 'encrypted-test-value',
            'use_ssl' => false,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return NetworkRouter::findOrFail(999);
    }
}
