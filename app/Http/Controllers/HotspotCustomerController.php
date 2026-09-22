<?php

namespace App\Http\Controllers;

use App\Models\HotspotCustomer;
use App\Models\HotspotCustomerMessage;
use App\Models\HotspotManualSmsMessage;
use App\Models\HotspotPayment;
use App\Jobs\SendHotspotManualSmsJob;
use App\Services\HotspotCustomerBroadcastService;
use App\Services\HotspotPhoneService;
use App\Services\HotspotPaymentRecoveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HotspotCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $customerStatus = (string) $request->query('status', 'active');
        if (! in_array($customerStatus, ['active', 'archived', 'all'], true)) {
            $customerStatus = 'active';
        }

        $customers = HotspotCustomer::query()
            ->when($customerStatus === 'active', fn ($query) => $query->where('active', true))
            ->when($customerStatus === 'archived', fn ($query) => $query->where('active', false))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('payment_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('normalized_phone', 'like', '%' . $search . '%');
            }))
            ->latest('last_paid_at')
            ->paginate(30)
            ->withQueryString();

        $customerCount = HotspotCustomer::where('active', true)->count();
        $smsEligibleCount = HotspotCustomer::where('active', true)
            ->where('sms_allowed', true)->count();
        $recentArchivedCount = HotspotCustomer::where('active', false)
            ->where('sms_allowed', true)
            ->whereNotNull('last_paid_at')
            ->where('last_paid_at', '>=', now()->subDays(30))
            ->count();
        $messageStats = HotspotCustomerMessage::whereDate(
            'campaign_date',
            now('Africa/Dar_es_Salaam')->toDateString()
        )->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $manualMessages = HotspotManualSmsMessage::with('customer')->latest()->limit(10)->get();
        $recoveryPayments = HotspotPayment::with(['customer', 'voucher'])
            ->where(function ($query) {
                $query->whereIn('status', ['voucher_failed', 'waiting_for_router', 'recovering'])
                    ->orWhere('voucher_sms_kind', 'recovery')
                    ->orWhere('voucher_recovery_attempts', '>', 0);
            })
            ->latest('paid_at')
            ->limit(20)
            ->get();

        $recoveryPayments->each(function (HotspotPayment $payment) {
            $voucher = $payment->voucher;
            $usageStatus = 'Not used';

            if ($voucher) {
                if (
                    $voucher->status === 'expired'
                    || ($voucher->expires_at && $voucher->expires_at->isPast())
                ) {
                    $usageStatus = 'Expired';
                } elseif (
                    $voucher->first_login_at
                    && $voucher->last_seen_at
                    && $voucher->last_seen_at->gte(now()->subMinutes(2))
                ) {
                    $usageStatus = 'Online';
                } elseif ($voucher->first_login_at) {
                    $usageStatus = 'Offline';
                }
            }

            $payment->setAttribute('recovery_usage_status', $usageStatus);
        });
        $contactOptions = HotspotCustomer::query()
            ->orderByDesc('active')
            ->orderBy('name')
            ->orderBy('normalized_phone')
            ->get(['id', 'name', 'payment_name', 'name_source', 'normalized_phone', 'active']);
        $manualSmsPrefill = session('manual_sms_prefill', []);

        return view('network.hotspot-customers.index', compact(
            'customers', 'customerCount', 'smsEligibleCount', 'messageStats',
            'manualMessages', 'contactOptions', 'recoveryPayments', 'search',
            'customerStatus', 'manualSmsPrefill', 'recentArchivedCount'
        ));
    }

    public function broadcast(
        Request $request,
        HotspotCustomerBroadcastService $service
    ): RedirectResponse {
        $data = $request->validate([
            'message_type' => ['required', Rule::in(['network_back', 'welcome_back'])],
            'audience' => ['required', Rule::in(['selected', 'active', 'recent_archived'])],
            'customer_ids' => ['required_if:audience,selected', 'array', 'min:1'],
            'customer_ids.*' => ['integer', 'distinct', 'exists:hotspot_customers,id'],
        ]);

        $result = $service->queue(
            $data['message_type'],
            $data['audience'],
            $data['customer_ids'] ?? []
        );

        return back()->with(
            $result['queued'] > 0 ? 'success' : 'error',
            'SMS queued: ' . $result['queued']
                . '. Already queued/sent today: ' . $result['already_sent_today'] . '.'
        );
    }

    public function manualSms(Request $request, HotspotPhoneService $phones): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:918'],
            'message_type' => [
                'required',
                Rule::in([
                    'custom',
                    'voucher',
                    'network_back',
                    'welcome_back',
                    'maintenance',
                ]),
            ],
        ]);

        if (
            $data['message_type'] === 'voucher'
            && str_contains($data['message'], '[VOUCHER]')
        ) {
            return back()
                ->withErrors([
                    'message' => 'Badilisha [VOUCHER] kwa voucher halisi kabla ya kutuma.',
                ])
                ->withInput();
        }

        try {
            $normalizedPhone = $phones->normalize($data['phone']);

            if (! $normalizedPhone) {
                throw new \InvalidArgumentException('Namba ya simu si sahihi.');
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['phone' => $e->getMessage()])->withInput();
        }

        $record = DB::transaction(function () use ($data, $normalizedPhone, $request) {
            $customer = HotspotCustomer::firstOrCreate(
                ['normalized_phone' => $normalizedPhone],
                [
                    'name' => trim($data['name']),
                    'name_source' => 'manual',
                    'phone' => $data['phone'],
                    'total_payments' => 0,
                    'total_amount' => 0,
                    'active' => true,
                    'sms_allowed' => true,
                ]
            );

            if (
                $data['message_type'] !== 'voucher'
                &&
                $customer->last_sms_at
                && $customer->last_sms_at
                    ->timezone('Africa/Dar_es_Salaam')
                    ->isSameDay(now('Africa/Dar_es_Salaam'))
            ) {
                return null;
            }

            return HotspotManualSmsMessage::create([
                'hotspot_customer_id' => $customer->id,
                'phone' => $data['phone'],
                'normalized_phone' => $normalizedPhone,
                'message' => trim($data['message']),
                'message_type' => $data['message_type'],
                'status' => 'pending',
                'requested_by' => $request->user()?->id,
            ]);
        });

        if (! $record) {
            return back()->with(
                'error',
                'SMS haikutumwa: customer huyu tayari amepokea SMS leo.'
            )->withInput();
        }

        SendHotspotManualSmsJob::dispatch($record->id);

        return back()->with('success', 'Manual SMS imewekwa kwenye foleni kwenda ' . $normalizedPhone . '.');
    }

    public function toggleSms(HotspotCustomer $hotspotCustomer): RedirectResponse
    {
        $hotspotCustomer->update(['sms_allowed' => ! $hotspotCustomer->sms_allowed]);

        return back()->with('success', 'SMS kwa ' . ($hotspotCustomer->name ?: $hotspotCustomer->normalized_phone)
            . ' sasa ni ' . ($hotspotCustomer->fresh()->sms_allowed ? 'ON.' : 'OFF.'));
    }

    public function toggleActive(HotspotCustomer $hotspotCustomer): RedirectResponse
    {
        if ($hotspotCustomer->active) {
            $hotspotCustomer->update([
                'active' => false,
                'archived_at' => now(),
                'archive_reason' => 'manual',
                'active_override_until' => null,
            ]);
        } else {
            $hotspotCustomer->update([
                'active' => true,
                'archived_at' => null,
                'archive_reason' => null,
                'active_override_until' => now()->addDays(3),
            ]);
        }

        return back()->with(
            'success',
            ($hotspotCustomer->fresh()->active ? 'Customer amerudishwa Active.' : 'Customer amewekwa Archived.')
        );
    }

    public function retryPayment(
        HotspotPayment $hotspotPayment,
        HotspotPaymentRecoveryService $recovery
    ): RedirectResponse {
        $result = $recovery->recover($hotspotPayment);

        if ($result['recovered']) {
            return back()->with('success', 'Voucher imetengenezwa na SMS imewekwa kwenye foleni.');
        }

        if ($result['already_completed']) {
            return back()->with('success', 'Payment hii tayari ina voucher.');
        }

        return back()->with('error', 'Retry imeshindikana: ' . ($result['error'] ?: 'jaribu tena baadaye.'));
    }
}
