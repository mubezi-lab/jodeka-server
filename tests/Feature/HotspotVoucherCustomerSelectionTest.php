<?php

namespace Tests\Feature;

use App\Http\Controllers\HotspotVoucherController;
use App\Http\Controllers\HotspotCustomerController;
use App\Jobs\SendHotspotManualSmsJob;
use App\Models\HotspotCustomer;
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
