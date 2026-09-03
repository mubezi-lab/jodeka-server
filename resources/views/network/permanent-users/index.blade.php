@extends('layouts.admin')

@section('title', 'Permanent Hotspot Users')

@section('content')
@php
    $formatBytes = function ($bytes) {
        $bytes = (int) $bytes;
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return number_format($bytes) . ' B';
    };
@endphp

<style>
    .permanent-page{font-family:Inter,ui-sans-serif,system-ui,sans-serif;color:#0f2747}
    .permanent-head,.permanent-actions,.panel-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .permanent-head h1{margin:0;font-size:30px}.permanent-head p{margin:5px 0 0;color:#64748b}
    .permanent-actions{justify-content:flex-end}.btn{display:inline-flex;align-items:center;gap:7px;border:0;border-radius:9px;padding:11px 15px;font-weight:800;text-decoration:none;cursor:pointer}
    .btn-primary{background:#2563eb;color:#fff}.btn-green{background:#059669;color:#fff}.btn-dark{background:#17375f;color:#fff}.btn-danger{background:#dc2626;color:#fff}.btn-light{background:#eaf1fb;color:#17375f}
    .notice{margin:18px 0;padding:12px 15px;border-radius:9px}.notice.success{background:#dcfce7;color:#166534}.notice.error{background:#fee2e2;color:#991b1b}
    .panel{margin-top:18px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 24px rgba(15,39,71,.06);overflow:hidden}
    .panel-head{padding:18px 20px;border-bottom:1px solid #e2e8f0}.panel-head h2{margin:0;font-size:19px}
    .form-grid{display:grid;grid-template-columns:repeat(6,minmax(130px,1fr));gap:12px;padding:18px 20px}
    label{display:block;font-size:12px;font-weight:800;margin-bottom:6px}.field{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:8px;padding:10px;background:#fff}
    .form-action{display:flex;align-items:end}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:1100px}th,td{padding:12px 14px;border-bottom:1px solid #e2e8f0;text-align:left;font-size:13px}th{background:#f8fafc;font-size:11px;text-transform:uppercase;color:#475569}
    .pill{display:inline-flex;padding:5px 9px;border-radius:999px;font-size:11px;font-weight:800}.online{background:#dcfce7;color:#047857}.offline{background:#e2e8f0;color:#475569}.staff{background:#dbeafe;color:#1d4ed8}.customer{background:#fef3c7;color:#a16207}.disabled{background:#fee2e2;color:#b91c1c}
    .money{font-weight:800;color:#b45309}.muted{color:#64748b}.row-actions{display:flex;gap:7px;align-items:center}.pay-form{display:flex;gap:6px}.pay-form input{width:90px}
    @media(max-width:1100px){.form-grid{grid-template-columns:repeat(2,minmax(150px,1fr))}}@media(max-width:650px){.form-grid{grid-template-columns:1fr}}
</style>

<div class="permanent-page">
    <div class="permanent-head">
        <div>
            <h1>Permanent Hotspot Users</h1>
            <p>Monitor staff and daily-paying customers who connect automatically.</p>
        </div>
        <div class="permanent-actions">
            <a class="btn btn-light" href="{{ route('hotspot-vouchers.index') }}">Vouchers</a>
            <form method="POST" action="{{ route('hotspot-permanent-users.sync') }}">
                @csrf
                <button class="btn btn-green" type="submit">Sync Now</button>
            </form>
        </div>
    </div>

    @if(session('success'))<div class="notice success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="notice error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif

    <section class="panel">
        <div class="panel-head"><h2>Add Permanent User</h2><span class="muted">MikroTik IP Binding must remain bypassed.</span></div>
        <form method="POST" action="{{ route('hotspot-permanent-users.store') }}" class="form-grid">
            @csrf
            <div><label>Router</label><select class="field" name="network_router_id" required>@foreach($routers as $router)<option value="{{ $router->id }}">{{ $router->name }}</option>@endforeach</select></div>
            <div><label>Name</label><input class="field" name="name" value="{{ old('name') }}" required></div>
            <div><label>Phone</label><input class="field" name="phone" value="{{ old('phone') }}" placeholder="07XXXXXXXX"></div>
            <div><label>MAC Address</label><input class="field" name="mac_address" value="{{ old('mac_address') }}" placeholder="AA:BB:CC:DD:EE:FF" required></div>
            <div><label>User Type</label><select class="field" name="user_type" required><option value="daily_customer">Daily Customer</option><option value="staff">Staff</option></select></div>
            <div><label>Daily Rate</label><input class="field" type="number" min="0" name="daily_rate" value="{{ old('daily_rate', 500) }}"></div>
            <div class="form-action"><button class="btn btn-primary" type="submit">Add User</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Users ({{ $users->count() }})</h2><span class="muted">Usage date: {{ $today }}</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>User</th><th>Type</th><th>Status</th><th>Phone / MAC</th><th>IP</th><th>Today's Usage</th><th>Last Seen</th><th>Outstanding</th><th>Actions</th></tr></thead>
                <tbody>
                @forelse($users as $user)
                    @php
                        $usage = $user->usages->first();
                        $totalBytes = (int)($usage?->bytes_in ?? 0) + (int)($usage?->bytes_out ?? 0);
                        $balance = (float)($user->outstanding_balance ?? 0) - (float)($user->outstanding_paid ?? 0);
                    @endphp
                    <tr>
                        <td><strong>{{ $user->name }}</strong><br><span class="muted">{{ $user->router?->name }}</span></td>
                        <td><span class="pill {{ $user->user_type === 'staff' ? 'staff' : 'customer' }}">{{ $user->user_type === 'staff' ? 'Staff' : 'Daily Customer' }}</span></td>
                        <td><span class="pill {{ !$user->enabled ? 'disabled' : ($user->is_online ? 'online' : 'offline') }}">{{ !$user->enabled ? 'Deactivated' : ($user->is_online ? 'Online' : 'Offline') }}</span></td>
                        <td>{{ $user->phone ?: '-' }}<br><span class="muted">{{ $user->mac_address }}</span></td>
                        <td>{{ $user->last_ip ?: '-' }}</td>
                        <td><strong>{{ $formatBytes($totalBytes) }}</strong><br><span class="muted">Up {{ $formatBytes($usage?->bytes_in ?? 0) }} / Down {{ $formatBytes($usage?->bytes_out ?? 0) }}</span></td>
                        <td>{{ $user->last_seen_at?->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="money">TZS {{ number_format(max(0, $balance), 0) }}</td>
                        <td>
                            <div class="row-actions">
                                @if($user->user_type === 'daily_customer')
                                <form class="pay-form" method="POST" action="{{ route('hotspot-permanent-users.payments.store', $user) }}">@csrf<input class="field" type="number" name="amount" min="1" value="500" required><button class="btn btn-green" type="submit">Pay</button></form>
                                @endif
                                <form method="POST" action="{{ route('hotspot-permanent-users.toggle', $user) }}" onsubmit="return confirm('{{ $user->enabled ? 'Deactivate this user and remove MikroTik internet bypass? Their history will remain.' : 'Reactivate this user and restore MikroTik internet bypass?' }}')">@csrf @method('PATCH')<button class="btn {{ $user->enabled ? 'btn-danger' : 'btn-green' }}" type="submit">{{ $user->enabled ? 'Deactivate' : 'Reactivate' }}</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" style="text-align:center;padding:35px">No permanent users registered.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
