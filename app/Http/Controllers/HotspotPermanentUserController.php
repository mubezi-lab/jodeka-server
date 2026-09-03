<?php

namespace App\Http\Controllers;

use App\Models\HotspotPermanentUser;
use App\Models\NetworkRouter;
use App\Services\HotspotPermanentPaymentService;
use App\Services\HotspotPermanentUsageService;
use App\Services\HotspotPhoneService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HotspotPermanentUserController extends Controller
{
    public function index(): View
    {
        $today = now('Africa/Dar_es_Salaam')->toDateString();
        $users = HotspotPermanentUser::with('router')
            ->with(['usages' => fn ($query) => $query->whereDate('usage_date', $today)])
            ->withSum([
                'charges as outstanding_balance' => fn ($query) => $query
                    ->whereIn('status', ['unpaid', 'partial'])
            ], 'amount')
            ->withSum([
                'charges as outstanding_paid' => fn ($query) => $query
                    ->whereIn('status', ['unpaid', 'partial'])
            ], 'paid_amount')
            ->orderByDesc('enabled')
            ->orderBy('name')
            ->get();

        $routers = NetworkRouter::where('enabled', true)->orderBy('name')->get();

        return view('network.permanent-users.index', compact('users', 'routers', 'today'));
    }

    public function store(
        Request $request,
        HotspotPhoneService $phones
    ): RedirectResponse {
        $data = $request->validate([
            'network_router_id' => ['required', 'exists:network_routers,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30', 'required_if:user_type,daily_customer'],
            'mac_address' => [
                'required',
                'regex:/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/',
                Rule::unique('hotspot_permanent_users')->where(
                    fn ($query) => $query->where('network_router_id', $request->network_router_id)
                ),
            ],
            'user_type' => ['required', Rule::in(['staff', 'daily_customer'])],
            'daily_rate' => ['required_if:user_type,daily_customer', 'nullable', 'numeric', 'min:0'],
        ]);

        $data['mac_address'] = strtoupper($data['mac_address']);
        $data['normalized_phone'] = $phones->normalize($data['phone'] ?? null);

        if (
            $data['normalized_phone']
            && HotspotPermanentUser::where(
                'normalized_phone',
                $data['normalized_phone']
            )->exists()
        ) {
            throw ValidationException::withMessages([
                'phone' => 'Namba hii tayari imesajiliwa kwa permanent user mwingine.',
            ]);
        }

        $data['daily_rate'] = $data['user_type'] === 'daily_customer'
            ? ($data['daily_rate'] ?? 500)
            : 0;
        $data['usage_threshold_bytes'] = 1048576;
        $data['enabled'] = true;

        HotspotPermanentUser::create($data);

        return back()->with('success', 'Permanent hotspot user added successfully.');
    }

    public function toggle(HotspotPermanentUser $hotspotPermanentUser): RedirectResponse
    {
        $hotspotPermanentUser->update([
            'enabled' => ! $hotspotPermanentUser->enabled,
            'is_online' => false,
        ]);

        return back()->with('success', 'Permanent user status updated.');
    }

    public function payment(
        Request $request,
        HotspotPermanentUser $hotspotPermanentUser,
        HotspotPermanentPaymentService $payments
    ): RedirectResponse {
        abort_unless($hotspotPermanentUser->user_type === 'daily_customer', 422);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $payments->recordOfficePayment(
            $hotspotPermanentUser,
            (float) $data['amount'],
            $request->user()?->id
        );

        return back()->with('success', 'Office payment recorded successfully.');
    }

    public function sync(HotspotPermanentUsageService $service): RedirectResponse
    {
        $result = $service->syncAll();

        return back()->with(
            $result['errors'] > 0 ? 'error' : 'success',
            'Permanent sync: online ' . $result['online']
            . ', charges created ' . $result['charges_created']
            . ', errors ' . $result['errors'] . '.'
        );
    }
}
