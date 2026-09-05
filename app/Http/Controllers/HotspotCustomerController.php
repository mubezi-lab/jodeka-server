<?php

namespace App\Http\Controllers;

use App\Models\HotspotCustomer;
use App\Models\HotspotCustomerMessage;
use App\Services\HotspotCustomerBroadcastService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HotspotCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $customers = HotspotCustomer::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('normalized_phone', 'like', '%' . $search . '%');
            }))
            ->latest('last_paid_at')
            ->paginate(30)
            ->withQueryString();

        $customerCount = HotspotCustomer::where('active', true)->count();
        $smsEligibleCount = HotspotCustomer::where('active', true)
            ->where('sms_allowed', true)->count();
        $messageStats = HotspotCustomerMessage::whereDate(
            'campaign_date',
            now('Africa/Dar_es_Salaam')->toDateString()
        )->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('network.hotspot-customers.index', compact(
            'customers', 'customerCount', 'smsEligibleCount', 'messageStats', 'search'
        ));
    }

    public function broadcast(
        Request $request,
        HotspotCustomerBroadcastService $service
    ): RedirectResponse {
        $data = $request->validate([
            'message_type' => ['required', Rule::in(['network_back', 'welcome_back'])],
        ]);

        $result = $service->queue($data['message_type']);

        return back()->with(
            $result['queued'] > 0 ? 'success' : 'error',
            'SMS queued: ' . $result['queued']
                . '. Already queued/sent today: ' . $result['already_sent_today'] . '.'
        );
    }
}
