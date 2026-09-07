@extends('layouts.admin')
@section('title', 'Daily Stock Closing')
@section('content')
    <div class="py-6 max-w-7xl mx-auto px-4 space-y-5">
        @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded">{{ session('success') }}</div>@endif
        @if($errors->any())
            <div class="bg-red-100 text-red-800 p-4 rounded">
                <ul>@foreach($errors->all() as $error)
                <li>{{ $error }}</li>@endforeach
                </ul>
        </div>@endif

        <div class="flex flex-wrap justify-between items-center gap-3">
            <div>
                <h2 class="text-2xl font-bold">Daily Stock Closing</h2>
                <p class="text-gray-600">Close stock, calculate daily sales and prepare automatic replenishment.</p>
            </div>
            <div class="flex gap-2">
                @unless($isEmployee)<button id="open-policy" class="bg-gray-700 text-white px-4 py-2 rounded">Stock
                Limits</button>@endunless
                <button id="open-close" class="bg-green-600 text-white px-4 py-2 rounded">Close Today's Stock</button>
            </div>
        </div>

        <form method="GET" class="bg-white rounded shadow p-3 flex flex-wrap gap-3 items-end">
            @unless($isEmployee)
                <div>
                    <label class="block text-sm text-gray-600">Branch</label>
                    <select name="business_id" class="border rounded p-2">
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}" @selected($selectedBusinessId == $business->id)>
                                {{ $business->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endunless

            <div>
                <label class="block text-sm text-gray-600">Report month</label>
                <select name="month" class="border rounded p-2 min-w-[180px]">
                    @foreach(range(0, 59) as $monthsAgo)
                        @php
                            $reportMonth = now()->startOfMonth()->subMonths($monthsAgo);
                            $reportMonthValue = $reportMonth->format('Y-m');
                        @endphp
                        <option value="{{ $reportMonthValue }}" @selected($month === $reportMonthValue)>
                            {{ $reportMonth->format('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded">View</button>
        </form>

        <div class="grid md:grid-cols-3 gap-4">
            <div class="bg-white shadow rounded p-4"><small class="text-gray-500">Monthly Revenue</small><strong
                    class="block text-xl">TZS {{ number_format($monthlyTotals['revenue'], 2) }}</strong></div>
            <div class="bg-white shadow rounded p-4"><small class="text-gray-500">Monthly COGS</small><strong
                    class="block text-xl">TZS {{ number_format($monthlyTotals['cogs'], 2) }}</strong></div>
            <div class="bg-white shadow rounded p-4"><small class="text-gray-500">Monthly Gross Profit</small><strong
                    class="block text-xl {{ $monthlyTotals['profit'] >= 0 ? 'text-green-700' : 'text-red-700' }}">TZS
                    {{ number_format($monthlyTotals['profit'], 2) }}</strong></div>
        </div>

        <div class="bg-white rounded shadow overflow-x-auto">
            <table class="w-full min-w-[850px] text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3">Date</th>
                        <th>Branch</th>
                        <th>Revenue</th>
                        <th>COGS</th>
                        <th>Gross Profit</th>
                        <th>Auto Request</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>@forelse($closings as $closing)
                    <tr class="border-t">
                        <td class="p-3">{{ $closing->closing_date->format('d/m/Y') }}</td>
                        <td>{{ $closing->business->name }}</td>
                        <td>TZS {{ number_format($closing->items->sum('revenue'), 2) }}</td>
                        <td>TZS {{ number_format($closing->items->sum('cost_of_goods_sold'), 2) }}</td>
                        <td
                            class="font-semibold {{ $closing->items->sum('gross_profit') >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            TZS {{ number_format($closing->items->sum('gross_profit'), 2) }}</td>
                        <td>{{ $closing->autoRequest?->request_number ?? '-' }}</td>
                        <td><a class="text-blue-700" href="{{ route('daily-stock.show', $closing) }}">View</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="7" class="p-6 text-center text-gray-500">No daily stock closing recorded.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="close-modal" class="hidden fixed inset-0 z-50 bg-black/50 p-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl mx-auto my-6 p-6">
            <div class="flex justify-between">
                <div>
                    <h3 class="font-bold text-xl">Close Daily Stock</h3>
                    <p id="close-progress" class="text-sm text-gray-500">Select branch and date</p>
                </div><button type="button" data-close="close-modal" class="text-2xl">&times;</button>
            </div>
            <form id="closing-form" method="POST" action="{{ route('daily-stock.store') }}">@csrf
                <section id="close-setup" class="mt-5">
                    @if($isEmployee)
                        <input type="hidden" id="close-business" name="business_id" value="{{ $selectedBusinessId }}">
                        <div class="bg-gray-50 border rounded p-3 mb-3"><small class="text-gray-500">Branch and
                                date</small><strong class="block">{{ $businesses->firstWhere('id', $selectedBusinessId)?->name }}
                                — {{ now()->format('d/m/Y') }}</strong></div><input type="hidden" id="close-date"
                            name="closing_date" value="{{ now()->toDateString() }}">
                    @else
                        <label class="block mb-1">Branch *</label><select id="close-business" name="business_id"
                            class="w-full border rounded p-2 mb-3">@foreach($businesses as $business)
                                <option value="{{ $business->id }}" @selected($selectedBusinessId == $business->id)>
                            {{ $business->name }}</option>@endforeach
                        </select>
                        <label class="block mb-1">Closing date *</label><input id="close-date" name="closing_date" type="date"
                            max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}"
                            class="w-full border rounded p-2">
                    @endif
                    <p id="setup-error" class="hidden text-red-600 mt-3"></p>
                    <div class="text-right mt-5"><button type="button" id="load-products"
                            class="bg-blue-600 text-white px-4 py-2 rounded">Start Counting</button></div>
                </section>
                <section id="close-item" class="hidden mt-5">
                    <label class="block mb-1 text-sm text-gray-600">Jump to product</label><select id="close-jump"
                        class="w-full border rounded p-2 mb-4"></select>
                    <div class="flex justify-between items-center mb-3"><strong id="item-name"
                            class="text-lg"></strong><span id="item-number" class="text-gray-500"></span></div>
                    <div class="grid grid-cols-2 gap-3 bg-gray-50 border rounded p-3 mb-4 text-sm">
                        <div>Opening: <strong id="item-opening"></strong></div>
                        <div>Received: <strong id="item-received"></strong></div>
                        <div>Available: <strong id="item-available"></strong></div>
                        <div>Minimum: <strong id="item-minimum"></strong></div>
                    </div>
                    <div id="first-opening-wrap" class="hidden grid grid-cols-2 gap-3 mb-3">
                        <div><label class="block mb-1">Initial opening units *</label><input id="item-first-opening"
                                type="number" min="0" step="0.01" class="w-full border rounded p-2"></div>
                        <div><label class="block mb-1">Opening cost/unit *</label><input id="item-opening-cost"
                                type="number" min="0" step="0.01" class="w-full border rounded p-2"></div>
                    </div>
                    <label class="block mb-1">Physical closing units *</label><input id="item-closing" type="number" min="0"
                        step="0.01" class="w-full border rounded p-2 mb-3">
                    <label class="block mb-1">Selling price per unit *</label><input id="item-price" type="number" min="0"
                        step="0.01" class="w-full border rounded p-2">
                    <p id="item-error" class="hidden text-red-600 mt-3"></p>
                    <div class="flex justify-between mt-5"><button type="button" id="item-back"
                            class="bg-gray-200 px-4 py-2 rounded">Previous</button><button type="button" id="item-next"
                            class="bg-blue-600 text-white px-4 py-2 rounded">Save & Next</button></div>
                </section>
                <section id="close-summary" class="hidden mt-5">
                    <h4 class="font-bold text-lg mb-3">Review Closing</h4>
                    <div id="summary-list" class="border rounded divide-y max-h-[50vh] overflow-y-auto"></div><label
                        class="block mt-4 mb-1">Notes</label><textarea name="notes"
                        class="w-full border rounded p-2"></textarea>
                    <div id="closing-inputs"></div>
                    <div class="flex justify-between mt-5"><button type="button" id="summary-back"
                            class="bg-gray-200 px-4 py-2 rounded">Back</button><button
                            class="bg-green-600 text-white px-4 py-2 rounded">Submit Daily Closing</button></div>
                </section>
            </form>
        </div>
    </div>

    @unless($isEmployee)
        <div id="policy-modal" class="hidden fixed inset-0 z-50 bg-black/50 p-4 overflow-y-auto">
            <div class="bg-white rounded-xl max-w-4xl mx-auto my-6 p-6">
                <div class="flex justify-between">
                    <div>
                        <h3 class="font-bold text-xl">Branch Stock Limits</h3>
                        <p class="text-gray-500">Enable products and set minimum/target units.</p>
                    </div><button type="button" data-close="policy-modal" class="text-2xl">&times;</button>
                </div>
                <form id="policy-form" method="POST" action="{{ route('daily-stock.policies.store') }}">@csrf<input
                        type="hidden" name="business_id" value="{{ $selectedBusinessId }}"><input id="policy-search"
                        class="w-full border rounded p-2 my-4" placeholder="Search product">
                    <div id="policy-list" class="border rounded divide-y max-h-[60vh] overflow-y-auto"></div>
                    <div class="text-right mt-4"><button class="bg-blue-600 text-white px-4 py-2 rounded">Save Stock
                            Limits</button></div>
                </form>
            </div>
        </div>
    @endunless

    <script>
        (() => {
            const closeModal = document.getElementById('close-modal'), setup = document.getElementById('close-setup'), itemStep = document.getElementById('close-item'), summary = document.getElementById('close-summary'), progress = document.getElementById('close-progress');
            let items = [], position = 0; const completed = new Set();
            const n = v => Number(v || 0), esc = v => String(v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]));
            function open(id) { document.getElementById(id).classList.remove('hidden') } function close(id) { document.getElementById(id).classList.add('hidden') }
            document.getElementById('open-close').onclick = () => open('close-modal'); document.querySelectorAll('[data-close]').forEach(b => b.onclick = () => close(b.dataset.close));
            document.getElementById('load-products').onclick = async () => { const error = document.getElementById('setup-error'); error.classList.add('hidden'); const business = document.getElementById('close-business').value, date = document.getElementById('close-date').value; try { const response = await fetch(`{{ route('daily-stock.context') }}?business_id=${encodeURIComponent(business)}&closing_date=${encodeURIComponent(date)}`, { headers: { 'Accept': 'application/json' } }); const data = await response.json(); if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message); if (!data.items.length) throw new Error('Hakuna products zilizowezeshwa. Manager/Admin aanze kwenye Stock Limits.'); items = data.items.map(x => ({ ...x, closing_units: '', chosen_price: x.selling_price, chosen_opening: x.opening_units, chosen_cost: x.opening_cost_per_unit })); position = 0; completed.clear(); const jump = document.getElementById('close-jump'); jump.innerHTML = items.map((x, i) => `<option value="${i}">${i + 1}. ${esc(x.name)}</option>`).join(''); setup.classList.add('hidden'); itemStep.classList.remove('hidden'); showItem() } catch (e) { error.textContent = e.message; error.classList.remove('hidden') } };
            function showItem() { const x = items[position], opening = x.has_previous ? n(x.opening_units) : n(x.chosen_opening), available = opening + n(x.received_units); document.getElementById('close-jump').value = position; document.getElementById('item-name').textContent = x.name; document.getElementById('item-number').textContent = `${position + 1} / ${items.length} · ${completed.size} completed`; document.getElementById('item-opening').textContent = opening; document.getElementById('item-received').textContent = n(x.received_units); document.getElementById('item-available').textContent = available; document.getElementById('item-minimum').textContent = n(x.minimum_units); document.getElementById('item-closing').value = x.closing_units; document.getElementById('item-price').value = x.chosen_price; const first = document.getElementById('first-opening-wrap'); first.classList.toggle('hidden', x.has_previous); document.getElementById('item-first-opening').value = x.chosen_opening; document.getElementById('item-opening-cost').value = x.chosen_cost; document.getElementById('item-back').disabled = position === 0; progress.textContent = `Count product ${position + 1} of ${items.length}`; document.getElementById('item-error').classList.add('hidden') }
            function capture() { const x = items[position], error = document.getElementById('item-error'); x.closing_units = document.getElementById('item-closing').value; x.chosen_price = document.getElementById('item-price').value; if (!x.has_previous) { x.chosen_opening = document.getElementById('item-first-opening').value; x.chosen_cost = document.getElementById('item-opening-cost').value } const available = (x.has_previous ? n(x.opening_units) : n(x.chosen_opening)) + n(x.received_units); if (x.closing_units === '' || n(x.closing_units) < 0 || x.chosen_price === '' || n(x.chosen_price) < 0) { error.textContent = 'Weka closing units na selling price.'; error.classList.remove('hidden'); return false } if (n(x.closing_units) > available) { error.textContent = `Closing haiwezi kuzidi available units ${available}.`; error.classList.remove('hidden'); return false } completed.add(position); return true }
            document.getElementById('item-next').onclick = () => { if (!capture()) return; if (position < items.length - 1) { position++; showItem() } else renderSummary() }; document.getElementById('item-back').onclick = () => { if (capture() && position > 0) { position--; showItem() } }; document.getElementById('summary-back').onclick = () => { summary.classList.add('hidden'); itemStep.classList.remove('hidden'); showItem() };
            document.getElementById('close-jump').onchange = event => { const destination = Number(event.target.value); if (capture()) { position = destination; showItem() } else event.target.value = position }; document.getElementById('closing-form').addEventListener('keydown', event => { if (event.key === 'Enter' && itemStep && !itemStep.classList.contains('hidden') && event.target.tagName !== 'TEXTAREA' && event.target.id !== 'close-jump') { event.preventDefault(); document.getElementById('item-next').click() } });
            function renderSummary() { itemStep.classList.add('hidden'); summary.classList.remove('hidden'); progress.textContent = 'Final summary'; const list = document.getElementById('summary-list'), inputs = document.getElementById('closing-inputs'); list.innerHTML = ''; inputs.innerHTML = ''; items.forEach((x, i) => { const opening = x.has_previous ? n(x.opening_units) : n(x.chosen_opening), available = opening + n(x.received_units), sold = available - n(x.closing_units), revenue = sold * n(x.chosen_price); list.insertAdjacentHTML('beforeend', `<div class="p-3"><strong>${esc(x.name)}</strong><div class="text-sm text-gray-600">Available ${available} − Closing ${n(x.closing_units)} = Sold ${sold}; Revenue TZS ${revenue.toLocaleString()}</div></div>`); inputs.insertAdjacentHTML('beforeend', `<input type="hidden" name="items[${i}][product_id]" value="${x.product_id}"><input type="hidden" name="items[${i}][opening_units]" value="${esc(x.chosen_opening)}"><input type="hidden" name="items[${i}][opening_cost_per_unit]" value="${esc(x.chosen_cost)}"><input type="hidden" name="items[${i}][closing_units]" value="${esc(x.closing_units)}"><input type="hidden" name="items[${i}][selling_price]" value="${esc(x.chosen_price)}">`) }) }
            @unless($isEmployee)
                const policyModal = document.getElementById('policy-modal'), policyList = document.getElementById('policy-list'); let policies = []; document.getElementById('open-policy').onclick = async () => { open('policy-modal'); policyList.innerHTML = '<p class="p-4">Loading...</p>'; const response = await fetch(`{{ route('daily-stock.policies') }}?business_id={{ $selectedBusinessId }}`, { headers: { 'Accept': 'application/json' } }); policies = await response.json(); renderPolicies('') }; document.getElementById('policy-search').oninput = e => renderPolicies(e.target.value.toLowerCase()); function renderPolicies(search) { policyList.innerHTML = ''; policies.forEach((p, i) => { if (!p.name.toLowerCase().includes(search)) return; policyList.insertAdjacentHTML('beforeend', `<div class="policy-row p-3 grid md:grid-cols-[1fr_120px_140px_140px] gap-3 items-center"><label><input type="hidden" name="policies[${i}][product_id]" value="${p.product_id}"><input type="hidden" name="policies[${i}][is_active]" value="0"><input type="checkbox" name="policies[${i}][is_active]" value="1" ${p.is_active ? 'checked' : ''}> <strong>${esc(p.name)}</strong><small class="block text-gray-500">${p.units_per_package} units/package</small></label><span>Minimum units</span><input name="policies[${i}][minimum_units]" type="number" min="0" step="0.01" value="${p.minimum_units}" class="border rounded p-2"><input name="policies[${i}][target_units]" type="number" min="0" step="0.01" value="${p.target_units}" class="border rounded p-2" title="Target units"></div>`) }) }
            @endunless
    })();
    </script>
@endsection