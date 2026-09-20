<?php

namespace App\Http\Controllers;

use App\Models\HotspotCustomer;
use App\Models\HotspotCustomerMessage;
use App\Models\HotspotManualSmsMessage;
use App\Jobs\SendHotspotManualSmsJob;
use App\Services\HotspotCustomerBroadcastService;
use App\Services\HotspotPhoneService;
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
        $manualMessages = HotspotManualSmsMessage::latest()->limit(10)->get();

        return view('network.hotspot-customers.index', compact(
            'customers', 'customerCount', 'smsEligibleCount', 'messageStats', 'manualMessages', 'search'
        ));
    }

    public function broadcast(
        Request $request,
        HotspotCustomerBroadcastService $service
    ): RedirectResponse {
        $data = $request->validate([
            'message_type' => ['required', Rule::in(['network_back', 'welcome_back'])],
            'customer_ids' => ['required', 'array', 'min:1'],
            'customer_ids.*' => ['integer', 'distinct', 'exists:hotspot_customers,id'],
        ]);

        $result = $service->queue($data['message_type'], $data['customer_ids']);

        return back()->with(
            $result['queued'] > 0 ? 'success' : 'error',
            'SMS queued: ' . $result['queued']
                . '. Already queued/sent today: ' . $result['already_sent_today'] . '.'
        );
    }

    public function manualSms(Request $request, HotspotPhoneService $phones): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:918'],
        ]);

        try {
            $normalizedPhone = $phones->normalize($data['phone']);

            if (! $normalizedPhone) {
                throw new \InvalidArgumentException('Namba ya simu si sahihi.');
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['phone' => $e->getMessage()])->withInput();
        }

        $record = HotspotManualSmsMessage::create([
            'phone' => $data['phone'],
            'normalized_phone' => $normalizedPhone,
            'message' => trim($data['message']),
            'status' => 'pending',
            'requested_by' => $request->user()?->id,
        ]);

        SendHotspotManualSmsJob::dispatch($record->id);

        return back()->with('success', 'Manual SMS imewekwa kwenye foleni kwenda ' . $normalizedPhone . '.');
    }

    public function toggleSms(HotspotCustomer $hotspotCustomer): RedirectResponse
    {
        $hotspotCustomer->update(['sms_allowed' => ! $hotspotCustomer->sms_allowed]);

        return back()->with('success', 'SMS kwa ' . ($hotspotCustomer->name ?: $hotspotCustomer->normalized_phone)
            . ' sasa ni ' . ($hotspotCustomer->fresh()->sms_allowed ? 'ON.' : 'OFF.'));
    }
}
