<?php

namespace Tests\Feature;

use App\Http\Controllers\HotspotVoucherController;
use App\Http\Controllers\HotspotCustomerController;
use App\Jobs\SendHotspotManualSmsJob;
use App\Models\HotspotCustomer;
use App\Models\HotspotCustomerMessage;
use App\Services\HotspotCustomerBroadcastService;
use App\Services\HotspotPhoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class HotspotVoucherCustomerSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_voucher_page_receives_saved_hotspot_customers(): void
    {
        $customer = HotspotCustomer::create([
            'name' => 'JACKSON KAIKA',
            'phone' => '0659840000',
            'normalized_phone' => '255659840000',
            'total_payments' => 0,
            'total_amount' => 0,
            'active' => true,
            'sms_allowed' => true,
        ]);

        $view = app(HotspotVoucherController::class)->create();
        $customerOptions = $view->getData()['customerOptions'];

        $this->assertTrue($customerOptions->contains('id', $customer->id));
        $this->assertSame(
            '255659840000',
            $customerOptions->firstWhere('id', $customer->id)->normalized_phone
        );
    }

    public function test_manual_sms_contacts_include_archived_customers(): void
    {
        $customer = HotspotCustomer::create([
            'name' => 'ARCHIVED CUSTOMER',
            'phone' => '0712345678',
            'normalized_phone' => '255712345678',
            'total_payments' => 1,
            'total_amount' => 500,
            'active' => false,
            'sms_allowed' => true,
            'last_paid_at' => now()->subDays(5),
        ]);

        $request = Request::create('/hotspot-customers?status=all', 'GET');
        $view = app(HotspotCustomerController::class)->index($request);

        $this->assertTrue($view->getData()['contactOptions']->contains('id', $customer->id));
    }

    public function test_recent_archived_audience_can_be_queued_manually(): void
    {
        Queue::fake();

        $recent = HotspotCustomer::create([
            'name' => 'RECENT ARCHIVED',
            'phone' => '0712345678',
            'normalized_phone' => '255712345678',
            'total_payments' => 1,
            'total_amount' => 500,
            'active' => false,
            'sms_allowed' => true,
            'last_paid_at' => now()->subDays(5),
        ]);

        HotspotCustomer::create([
            'name' => 'OLD ARCHIVED',
            'phone' => '0711111111',
            'normalized_phone' => '255711111111',
            'total_payments' => 1,
            'total_amount' => 500,
            'active' => false,
            'sms_allowed' => true,
            'last_paid_at' => now()->subDays(40),
        ]);

        $result = app(HotspotCustomerBroadcastService::class)
            ->queue('network_back', 'recent_archived');

        $this->assertSame(1, $result['queued']);
        $this->assertDatabaseHas('hotspot_customer_messages', [
            'hotspot_customer_id' => $recent->id,
            'status' => 'pending',
        ]);
        $this->assertSame(1, HotspotCustomerMessage::count());
    }

    public function test_manual_sms_page_contains_editable_voucher_template(): void
    {
        $template = file_get_contents(
            resource_path('views/network/hotspot-customers/index.blade.php')
        );

        $this->assertStringContainsString(
            'Karibu JODEKA Hotspot, voucher yako ni [VOUCHER]. Endelea kupata huduma bora ya WiFi.',
            $template
        );
    }

    public function test_voucher_message_is_not_blocked_by_promotional_daily_limit(): void
    {
        Queue::fake();

        HotspotCustomer::create([
            'name' => 'JACKSON KAIKA',
            'phone' => '0659840000',
            'normalized_phone' => '255659840000',
            'total_payments' => 0,
            'total_amount' => 0,
            'active' => true,
            'sms_allowed' => true,
            'last_sms_at' => now(),
        ]);

        $request = Request::create('/hotspot-customers/manual-sms', 'POST', [
            'name' => 'JACKSON KAIKA',
            'phone' => '0659840000',
            'message_type' => 'voucher',
            'message' => 'Karibu JODEKA Hotspot, voucher yako ni JDK12345. Endelea kupata huduma bora ya WiFi.',
        ]);

        app(HotspotCustomerController::class)->manualSms(
            $request,
            app(HotspotPhoneService::class)
        );

        $this->assertDatabaseHas('hotspot_manual_sms_messages', [
            'normalized_phone' => '255659840000',
            'status' => 'pending',
        ]);
        Queue::assertPushed(SendHotspotManualSmsJob::class, 1);
    }
}
