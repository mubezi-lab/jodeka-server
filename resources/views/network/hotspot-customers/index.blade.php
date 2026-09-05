@extends('layouts.admin')

@section('title', 'Hotspot Customers')

@section('content')
<style>
    .hc{color:#0f2747}.hc-head,.hc-actions,.hc-stats{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.hc h1{margin:0}.muted{color:#64748b}.panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin-top:18px;overflow:hidden}.pad{padding:18px}.btn{border:0;border-radius:9px;padding:11px 15px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-block}.blue{background:#2563eb;color:white}.green{background:#059669;color:white}.dark{background:#17375f;color:white}.orange{background:#ea580c;color:white}.notice{padding:12px 15px;border-radius:9px;margin-top:15px}.success{background:#dcfce7;color:#166534}.error{background:#fee2e2;color:#991b1b}.stat{background:#f8fafc;padding:12px 16px;border-radius:10px}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:900px}th,td{padding:12px 14px;border-bottom:1px solid #e2e8f0;text-align:left}th{background:#f8fafc;font-size:12px}.search{display:flex;gap:8px}.search input{border:1px solid #cbd5e1;border-radius:8px;padding:10px;min-width:260px}
</style>

<div class="hc">
    <div class="hc-head">
        <div><h1>Hotspot Customers</h1><p class="muted">Wateja waliowahi kununua voucher kupitia Lipa Namba.</p></div>
        <a class="btn dark" href="{{ route('hotspot-vouchers.index') }}">Vouchers</a>
    </div>

    @if(session('success'))<div class="notice success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="notice error">{{ session('error') }}</div>@endif

    <div class="panel pad">
        <div class="hc-stats">
            <div class="stat"><strong>{{ $customerCount }}</strong><br><span class="muted">Active customers</span></div>
            <div class="stat"><strong>{{ $smsEligibleCount }}</strong><br><span class="muted">SMS eligible</span></div>
            <div class="stat"><strong>{{ $messageStats->get('sent', 0) }}</strong><br><span class="muted">Sent today</span></div>
            <div class="stat"><strong>{{ $messageStats->get('failed', 0) }}</strong><br><span class="muted">Failed today</span></div>
        </div>
        <div class="hc-actions" style="margin-top:16px">
            <form method="POST" action="{{ route('hotspot-customers.broadcasts.store') }}" onsubmit="return confirm('Tuma ujumbe wa mtandao kurudi kwa customers {{ $smsEligibleCount }}?')">@csrf<input type="hidden" name="message_type" value="network_back"><button class="btn green" type="submit">Network Is Back</button></form>
            <form method="POST" action="{{ route('hotspot-customers.broadcasts.store') }}" onsubmit="return confirm('Tuma ujumbe wa kuwakaribisha customers {{ $smsEligibleCount }}?')">@csrf<input type="hidden" name="message_type" value="welcome_back"><button class="btn orange" type="submit">Welcome Back</button></form>
            <form class="search" method="GET"><input name="q" value="{{ $search }}" placeholder="Search name or phone"><button class="btn blue">Search</button></form>
        </div>
    </div>

    <div class="panel table-wrap">
        <table>
            <thead><tr><th>Customer</th><th>Phone</th><th>First Payment</th><th>Last Payment</th><th>Payments</th><th>Total Amount</th><th>Last SMS</th></tr></thead>
            <tbody>
            @forelse($customers as $customer)
                <tr>
                    <td><strong>{{ $customer->name ?: '-' }}</strong></td>
                    <td>{{ $customer->normalized_phone }}</td>
                    <td>{{ $customer->first_paid_at?->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $customer->last_paid_at?->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ number_format($customer->total_payments) }}</td>
                    <td>TZS {{ number_format((float) $customer->total_amount, 0) }}</td>
                    <td>{{ $customer->last_sms_at?->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;padding:30px">No hotspot customers found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="pad">{{ $customers->links() }}</div>
    </div>
</div>
@endsection
