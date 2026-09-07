@extends('layouts.admin')
@section('title', 'Stock Requests & Procurement')
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
                <h2 class="text-2xl font-bold">Stock Requests</h2>
                <p class="text-gray-600">Create and follow branch stock requests.</p>
            </div>
            <button type="button" id="open-request-modal" class="bg-blue-600 text-white px-4 py-2 rounded">+ New Stock
                Request</button>
        </div>
        <form method="GET" class="bg-white shadow rounded p-4 grid md:grid-cols-6 gap-3 items-end">
            <div><label class="block text-sm text-gray-600">Request number</label><input name="search"
                    value="{{ request('search') }}" class="w-full border rounded p-2" placeholder="REQ / AUTO"></div>
            <div><label class="block text-sm text-gray-600">Branch</label><select name="business_id"
                    class="w-full border rounded p-2">
                    <option value="">All branches</option>@foreach($businesses as $business)
                        <option value="{{ $business->id }}" @selected(request('business_id') == $business->id)>
                    {{ $business->name }}</option>@endforeach
                </select></div>
            <div><label class="block text-sm text-gray-600">Source</label><select name="source"
                    class="w-full border rounded p-2">
                    <option value="">Manual & Auto</option>
                    <option value="manual" @selected(request('source') === 'manual')>Manual</option>
                    <option value="auto" @selected(request('source') === 'auto')>Auto</option>
                </select></div>
            <div><label class="block text-sm text-gray-600">Status</label><select name="status"
                    class="w-full border rounded p-2">
                    <option value="">All statuses</option>
                    @foreach(['draft', 'pending', 'approved', 'rejected', 'ordered', 'partially_received', 'received'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                    {{ ucwords(str_replace('_', ' ', $status)) }}</option>@endforeach
                </select></div>
            <div>
                <label class="block text-sm text-gray-600">Month</label>
                <select name="month" class="w-full border rounded p-2">
                    <option value="">All months</option>
                    @foreach(range(0, 59) as $monthsAgo)
                        @php
                            $filterMonth = now()->startOfMonth()->subMonths($monthsAgo);
                            $filterMonthValue = $filterMonth->format('Y-m');
                        @endphp
                        <option value="{{ $filterMonthValue }}" @selected(request('month') === $filterMonthValue)>
                            {{ $filterMonth->format('F Y') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2"><button class="bg-blue-600 text-white px-4 py-2 rounded">Filter</button><a
                    href="{{ route('procurement.index') }}" class="bg-gray-200 px-4 py-2 rounded">Reset</a></div>
        </form>
        <div class="bg-white shadow rounded overflow-x-auto">
            <table class="w-full text-left min-w-[760px]">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3">Request</th>
                        <th>Branch</th>
                        <th>Date</th>
                        <th>By</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>@forelse($requests as $row)
                    <tr class="border-t">
                        <td class="p-3">{{ $row->request_number }} @if(($row->source ?? 'manual') === 'auto')<span
                        class="text-xs bg-blue-100 text-blue-700 rounded px-2 py-1">AUTO</span>@endif</td>
                        <td>{{ $row->business->name }}</td>
                        <td>{{ $row->request_date->format('d/m/Y') }}</td>
                        <td>{{ $row->requester->name }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $row->status)) }}</td>
                        <td><a class="text-blue-700" href="{{ route('procurement.requests.show', $row) }}">View</a></td>
                    </tr>
                @empty<tr>
                        <td colspan="6" class="p-6 text-center text-gray-500">Hakuna stock request bado.</td>
                    </tr>@endforelse
                </tbody>
            </table>
            <div class="p-3">{{ $requests->links() }}</div>
        </div>
    </div>

    <div id="request-modal" class="hidden fixed inset-0 z-50 bg-black/50 p-4 overflow-y-auto">
        <div class="bg-white rounded-xl shadow-xl max-w-2xl mx-auto my-6 p-6">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="font-bold text-xl">New Stock Request</h3>
                    <p id="request-progress" class="text-sm text-gray-500">Request details</p>
                </div><button type="button" id="close-request-modal" class="text-2xl text-gray-500">&times;</button>
            </div>
            <form id="stock-request-form" method="POST" action="{{ route('procurement.requests.store') }}">@csrf
                <section id="request-entry-step">
                    @if($fixedBusiness)
                        <div class="bg-gray-50 border rounded p-3 mb-3"><span class="text-gray-500 text-sm">Branch</span><strong
                                class="block">{{ $fixedBusiness->name }}</strong></div><input type="hidden" name="business_id"
                            value="{{ $fixedBusiness->id }}">
                    @else
                        <label class="block mb-1">Branch *</label><select name="business_id"
                            class="w-full border rounded p-2 mb-3" required>
                            <option value="">Select branch</option>@foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>@endforeach
                        </select>
                    @endif
                    @if($canBackdate)
                        <label class="block mb-1">Request Date *</label><input type="date" name="request_date"
                            value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" class="w-full border rounded p-2 mb-4"
                            required>
                    @else
                        <div class="bg-gray-50 border rounded p-3 mb-4"><span class="text-gray-500 text-sm">Request
                                Date</span><strong class="block">{{ now()->format('d/m/Y') }}</strong></div>
                    @endif
                    <div class="border rounded p-3 mb-4 bg-gray-50">
                        <label class="block mb-1">Product *</label><select id="product-picker"
                            class="w-full border rounded p-2 mb-2">
                            <option value="">Select product</option>@foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->package_type }})</option>
                            @endforeach
                        </select>
                        <label class="block mb-1">Packages *</label><input id="quantity-picker"
                            class="w-full border rounded p-2 mb-2" type="number" min="0.01" step="0.01"
                            placeholder="Packages">
                        <label class="block mb-1">Item Notes</label><input id="notes-picker"
                            class="w-full border rounded p-2 mb-3" placeholder="Optional notes">
                        <button type="button" id="add-item" class="w-full bg-blue-600 text-white rounded p-2">Add
                            Product</button>
                    </div>
                    <div id="selected-wrapper" class="hidden mb-4">
                        <div class="flex justify-between mb-2">
                            <h4 class="font-semibold">Selected Products</h4><span id="selected-count"
                                class="text-sm text-gray-500"></span>
                        </div>
                        <div id="request-items" class="space-y-2 max-h-56 overflow-y-auto pr-1"></div>
                    </div>
                    <label class="block mb-1">General Notes</label><textarea name="notes" class="w-full border rounded p-2"
                        placeholder="Optional notes"></textarea>
                </section>
                <section id="request-summary-step" class="hidden">
                    <h4 class="font-bold text-lg mb-3">Confirm Stock Request</h4>
                    <div id="request-summary" class="border rounded divide-y max-h-[55vh] overflow-y-auto"></div>
                </section>
                <p id="item-error" class="hidden text-red-600 text-sm mt-3"></p>
                <div class="flex justify-between mt-6"><button type="button" id="request-back"
                        class="hidden bg-gray-200 px-4 py-2 rounded">Back</button><button type="button" id="request-review"
                        class="ml-auto bg-blue-600 text-white px-4 py-2 rounded">Review Request</button><button
                        type="submit" id="request-submit"
                        class="hidden ml-auto bg-green-600 text-white px-4 py-2 rounded">Submit Request</button></div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.getElementById('request-modal'), form = document.getElementById('stock-request-form'), entry = document.getElementById('request-entry-step'), summaryStep = document.getElementById('request-summary-step'), holder = document.getElementById('request-items'), wrapper = document.getElementById('selected-wrapper'), count = document.getElementById('selected-count'), product = document.getElementById('product-picker'), quantity = document.getElementById('quantity-picker'), notes = document.getElementById('notes-picker'), error = document.getElementById('item-error'), review = document.getElementById('request-review'), back = document.getElementById('request-back'), submit = document.getElementById('request-submit'); let items = [];
            const escapeHtml = value => String(value).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
            function showError(message) { error.textContent = message; error.classList.remove('hidden') }
            function render() { holder.innerHTML = ''; wrapper.classList.toggle('hidden', items.length === 0); count.textContent = `${items.length} selected`; items.forEach((item, index) => { const row = document.createElement('div'); row.className = 'border rounded p-3 flex items-center justify-between gap-3'; row.innerHTML = `<div><strong>${escapeHtml(item.name)}</strong><div class="text-sm text-gray-600">Packages: ${escapeHtml(item.quantity)}${item.notes ? ' — ' + escapeHtml(item.notes) : ''}</div></div><div class="flex gap-2"><button type="button" class="edit text-blue-700">Edit</button><button type="button" class="remove text-red-600">Remove</button></div><input type="hidden" name="items[${index}][product_id]" value="${escapeHtml(item.productId)}"><input type="hidden" name="items[${index}][quantity]" value="${escapeHtml(item.quantity)}"><input type="hidden" name="items[${index}][notes]" value="${escapeHtml(item.notes)}">`; row.querySelector('.remove').onclick = () => { items.splice(index, 1); render() }; row.querySelector('.edit').onclick = () => { product.value = item.productId; quantity.value = item.quantity; notes.value = item.notes; items.splice(index, 1); render(); product.focus() }; holder.appendChild(row) }) }
            document.getElementById('add-item').onclick = () => { error.classList.add('hidden'); if (!product.value || !quantity.value || Number(quantity.value) <= 0) return showError('Chagua product na uweke packages zaidi ya sifuri.'); if (items.some(item => item.productId === product.value)) return showError('Product hii tayari ipo. Tumia Edit kubadilisha packages.'); items.push({ productId: product.value, name: product.selectedOptions[0].text, quantity: quantity.value, notes: notes.value.trim() }); product.value = ''; quantity.value = ''; notes.value = ''; render(); product.focus() };
            review.onclick = () => { error.classList.add('hidden'); if (items.length === 0) return showError('Ongeza angalau product moja.'); for (const field of form.querySelectorAll('[name="business_id"],[name="request_date"]')) { if (!field.checkValidity()) { field.reportValidity(); return } } const box = document.getElementById('request-summary'); box.innerHTML = ''; items.forEach(item => { const row = document.createElement('div'), title = document.createElement('strong'), detail = document.createElement('span'); row.className = 'p-3 flex justify-between gap-3'; title.textContent = item.name; detail.textContent = `${item.quantity} packages`; row.append(title, detail); box.appendChild(row) }); entry.classList.add('hidden'); summaryStep.classList.remove('hidden'); review.classList.add('hidden'); back.classList.remove('hidden'); submit.classList.remove('hidden'); document.getElementById('request-progress').textContent = 'Final summary' };
            back.onclick = () => { entry.classList.remove('hidden'); summaryStep.classList.add('hidden'); review.classList.remove('hidden'); back.classList.add('hidden'); submit.classList.add('hidden'); document.getElementById('request-progress').textContent = 'Request details' };
            document.getElementById('open-request-modal').onclick = () => modal.classList.remove('hidden'); document.getElementById('close-request-modal').onclick = () => modal.classList.add('hidden'); modal.addEventListener('click', event => { if (event.target === modal) modal.classList.add('hidden') }); form.addEventListener('submit', event => { if (items.length === 0) { event.preventDefault(); showError('Ongeza angalau product moja.') } });
        })();
    </script>
@endsection