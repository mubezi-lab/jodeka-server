@extends('layouts.admin')
@section('title', 'Hotspot Customers')
@section('content')
<style>
.hc{color:#0f2747}.row{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin-top:18px;overflow:hidden}.pad{padding:18px}.btn{border:0;border-radius:9px;padding:10px 14px;font-weight:800;cursor:pointer;text-decoration:none;display:inline-block}.blue{background:#2563eb;color:#fff}.green{background:#059669;color:#fff}.dark{background:#17375f;color:#fff}.orange{background:#ea580c;color:#fff}.red{background:#dc2626;color:#fff}.muted{color:#64748b}.notice{padding:12px 15px;border-radius:9px;margin-top:15px}.success{background:#dcfce7;color:#166534}.error{background:#fee2e2;color:#991b1b}.stat{background:#f8fafc;padding:12px 16px;border-radius:10px}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:1000px}th,td{padding:12px 14px;border-bottom:1px solid #e2e8f0;text-align:left;vertical-align:top}th{background:#f8fafc;font-size:12px}.field{border:1px solid #cbd5e1;border-radius:8px;padding:10px}.manual{display:grid;grid-template-columns:minmax(220px,1fr) minmax(320px,3fr) auto;gap:10px;align-items:start}.manual textarea{min-height:92px;resize:vertical}.status{font-weight:800}.sent{color:#047857}.failed{color:#b91c1c}.pending,.processing{color:#b45309}@media(max-width:800px){.manual{grid-template-columns:1fr}}
</style>
<div class="hc">
 <div class="row"><div><h1>Hotspot Customers</h1><p class="muted">Wateja waliowahi kununua voucher kupitia Lipa Namba.</p></div><a class="btn dark" href="{{ route('hotspot-vouchers.index') }}">Vouchers</a></div>
 @if(session('success'))<div class="notice success">{{ session('success') }}</div>@endif
 @if(session('error'))<div class="notice error">{{ session('error') }}</div>@endif
 @if($errors->any())<div class="notice error">{{ $errors->first() }}</div>@endif

 <div class="panel pad">
  <h2>Send Manual SMS</h2><p class="muted">Ingiza namba yoyote ya Tanzania na ujumbe. Sender atakuwa JODEKA.</p>
  <form method="POST" action="{{ route('hotspot-customers.manual-sms.store') }}" id="manualSmsForm" class="manual">@csrf
   <input class="field" name="phone" value="{{ old('phone') }}" placeholder="0659840000" required>
   <div><textarea class="field" style="width:100%" name="message" id="manualMessage" maxlength="918" placeholder="Andika ujumbe hapa..." required>{{ old('message') }}</textarea><span class="muted"><span id="charCount">0</span>/918 characters · takriban <span id="smsCount">1</span> SMS credit(s)</span></div>
   <button class="btn blue" type="submit">Send SMS</button>
  </form>
 </div>

 <div class="panel pad">
  <div class="row"><div class="stat"><strong>{{ $customerCount }}</strong><br><span class="muted">Active customers</span></div><div class="stat"><strong>{{ $smsEligibleCount }}</strong><br><span class="muted">SMS eligible</span></div><div class="stat"><strong>{{ $messageStats->get('sent',0) }}</strong><br><span class="muted">Sent today</span></div><div class="stat"><strong>{{ $messageStats->get('failed',0) }}</strong><br><span class="muted">Failed today</span></div></div>
  <form method="POST" action="{{ route('hotspot-customers.broadcasts.store') }}" id="broadcastForm">@csrf
   <div class="row" style="margin-top:16px"><div><button class="btn green broadcast" name="message_type" value="network_back">Network Is Back</button> <button class="btn orange broadcast" name="message_type" value="welcome_back">Welcome Back</button></div><label><input type="checkbox" id="selectAll"> Select All eligible on this page</label><strong><span id="selectedCount">0</span> selected</strong></div>
   <div class="table-wrap"><table><thead><tr><th>Select</th><th>Customer</th><th>Phone</th><th>Last Payment</th><th>Payments</th><th>Total</th><th>SMS</th><th>Last SMS</th></tr></thead><tbody>
   @forelse($customers as $customer)<tr>
    <td><input class="customer-check" type="checkbox" name="customer_ids[]" value="{{ $customer->id }}" @disabled(! $customer->active || ! $customer->sms_allowed)></td>
    <td><strong>{{ $customer->name ?: '-' }}</strong></td><td>{{ $customer->normalized_phone }}</td><td>{{ $customer->last_paid_at?->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') ?? '-' }}</td><td>{{ number_format($customer->total_payments) }}</td><td>TZS {{ number_format((float)$customer->total_amount,0) }}</td>
    <td>{{ $customer->sms_allowed ? 'ON' : 'OFF' }} <button class="btn {{ $customer->sms_allowed ? 'red' : 'green' }}" type="submit" form="toggle-{{ $customer->id }}">{{ $customer->sms_allowed ? 'Turn Off' : 'Turn On' }}</button></td><td>{{ $customer->last_sms_at?->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') ?? '-' }}</td>
   </tr>@empty<tr><td colspan="8" style="text-align:center;padding:30px">No hotspot customers found.</td></tr>@endforelse
   </tbody></table></div>
  </form>
  @foreach($customers as $customer)<form id="toggle-{{ $customer->id }}" method="POST" action="{{ route('hotspot-customers.sms.toggle',$customer) }}" onsubmit="return confirm('Badilisha ruhusa ya SMS kwa customer huyu?')">@csrf @method('PATCH')</form>@endforeach
  <div class="row pad"><form method="GET"><input class="field" name="q" value="{{ $search }}" placeholder="Search name or phone"> <button class="btn blue">Search</button></form><div>{{ $customers->links() }}</div></div>
 </div>

 <div class="panel table-wrap"><div class="pad"><h2>Recent Manual SMS</h2></div><table><thead><tr><th>Time</th><th>Phone</th><th>Message</th><th>Status</th><th>Attempts</th><th>Error</th></tr></thead><tbody>
 @forelse($manualMessages as $sms)<tr><td>{{ $sms->created_at?->timezone('Africa/Dar_es_Salaam')->format('d/m/Y H:i') }}</td><td>{{ $sms->normalized_phone }}</td><td>{{ $sms->message }}</td><td class="status {{ $sms->status }}">{{ ucfirst($sms->status) }}</td><td>{{ $sms->attempts }}</td><td>{{ $sms->error ?: '-' }}</td></tr>@empty<tr><td colspan="6" style="text-align:center;padding:25px">No manual SMS sent yet.</td></tr>@endforelse
 </tbody></table></div>
</div>
<script>
(()=>{const m=document.getElementById('manualMessage'),c=document.getElementById('charCount'),s=document.getElementById('smsCount');function len(){c.textContent=m.value.length;s.textContent=m.value.length<=160?1:Math.ceil(m.value.length/153)}m.addEventListener('input',len);len();document.getElementById('manualSmsForm').addEventListener('submit',e=>{if(!confirm(`Tuma SMS kwenda ${e.currentTarget.phone.value}?`))e.preventDefault()});const checks=[...document.querySelectorAll('.customer-check:not(:disabled)')],all=document.getElementById('selectAll'),count=document.getElementById('selectedCount');function tally(){const n=checks.filter(x=>x.checked).length;count.textContent=n;all.checked=checks.length>0&&n===checks.length}all.addEventListener('change',()=>{checks.forEach(x=>x.checked=all.checked);tally()});checks.forEach(x=>x.addEventListener('change',tally));document.querySelectorAll('.broadcast').forEach(b=>b.addEventListener('click',e=>{const n=checks.filter(x=>x.checked).length;if(!n||!confirm(`Tuma ${e.currentTarget.textContent.trim()} kwa customer(s) ${n}?`))e.preventDefault()}))})();
</script>
@endsection
