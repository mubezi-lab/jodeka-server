<?php

namespace Tests\Feature;

use App\Models\HotspotCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HotspotCustomerArchivingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_archives_only_customers_without_a_payment_for_three_days(): void
    {
        $old = HotspotCustomer::create([
            'name' => 'OLD CUSTOMER',
            'phone' => '0711111111',
            'normalized_phone' => '255711111111',
            'last_paid_at' => now()->subDays(4),
            'active' => true,
            'sms_allowed' => true,
        ]);

        $recent = HotspotCustomer::create([
            'name' => 'RECENT CUSTOMER',
            'phone' => '0722222222',
            'normalized_phone' => '255722222222',
            'last_paid_at' => now()->subDays(2),
            'active' => true,
            'sms_allowed' => true,
        ]);

        $this->artisan('hotspot:archive-inactive-customers --days=3')
            ->assertSuccessful();

        $this->assertFalse($old->fresh()->active);
        $this->assertTrue($recent->fresh()->active);
    }
}
