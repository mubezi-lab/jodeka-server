@extends('layouts.admin')
@section('title', 'Stock Requests & Procurement')
@section('content')
    <div class="py-6 max-w-7xl mx-auto px-4">
        @if(session('success'))<div class="bg-green-100 text-green-800 p-4 rounded mb-4">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="bg-red-100 text-red-800 p-4 rounded mb-4"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="grid lg:grid-cols-3 gap-6">
            <form method="POST" action="{{ route('procurement.requests.store') }}" class="bg-white shadow rounded p-5">
                @csrf
                <h3 class="font-bold text-lg mb-4">New Stock Request</h3>
                <label>Branch *</label>
                <select name="business_id" class="w-full border rounded p-2 mb-3" required>
                    <option value="">Select branch</option>@foreach($businesses as $business)<option value="{{ $business->id }}">{{ $business->name }}</option>@endforeach
                </select>
                <label>Request Date *</label><input type="date" name="request_date" value="{{ date('Y-m-d') }}" class="w-full border rounded p-2 mb-3" required>
                <div id="request-items" class="space-y-3"></div>
                <button type="button" id="add-item" class="text-blue-700 mb-3">+ Add product</button>
                <label>Notes</label><textarea name="notes" class="w-full border rounded p-2 mb-3"></textarea>
                <button class="w-full bg-green-600 text-white rounded p-2">Submit Request</button>
            </form>
            <div class="lg:col-span-2 bg-white shadow rounded overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-100"><tr><th class="p-3">Request</th><th>Branch</th><th>Date</th><th>By</th><th>Status</th><th></th></tr></thead>
                    <tbody>@forelse($requests as $row)
                        <tr class="border-t"><td class="p-3">{{ $row->request_number }}</td><td>{{ $row->business->name }}</td><td>{{ $row->request_date->format('d/m/Y') }}</td><td>{{ $row->requester->name }}</td><td>{{ ucwords(str_replace('_',' ',$row->status)) }}</td><td><a class="text-blue-700" href="{{ route('procurement.requests.show',$row) }}">View</a></td></tr>
                    @empty<tr><td colspan="6" class="p-6 text-center text-gray-500">Hakuna stock request bado.</td></tr>@endforelse</tbody>
                </table>
                <div class="p-3">{{ $requests->links() }}</div>
            </div>
        </div>
    </div>
    <template id="item-template"><div class="border rounded p-3 request-row"><select class="product w-full border rounded p-2 mb-2" required><option value="">Select product</option>@foreach($products as $product)<option value="{{ $product->id }}" data-units="{{ $product->units_per_package }}">{{ $product->name }} ({{ $product->package_type }})</option>@endforeach</select><input class="quantity w-full border rounded p-2 mb-2" type="number" min="0.01" step="0.01" placeholder="Packages" required><input class="notes w-full border rounded p-2" placeholder="Item notes"><button type="button" class="remove text-red-600 mt-2">Remove</button></div></template>
    <script>
        const holder=document.getElementById('request-items'), template=document.getElementById('item-template');
        function renumber(){[...holder.children].forEach((row,i)=>{row.querySelector('.product').name=`items[${i}][product_id]`;row.querySelector('.quantity').name=`items[${i}][quantity]`;row.querySelector('.notes').name=`items[${i}][notes]`;let units=row.querySelector('.product').selectedOptions[0]?.dataset.units||1;let hidden=row.querySelector('.units');if(!hidden){hidden=document.createElement('input');hidden.type='hidden';hidden.className='units';row.appendChild(hidden)}hidden.name=`items[${i}][units_per_package]`;hidden.value=units;});}
        function add(){let row=template.content.firstElementChild.cloneNode(true);row.querySelector('.remove').onclick=()=>{row.remove();renumber()};row.querySelector('.product').onchange=renumber;holder.appendChild(row);renumber()}
        document.getElementById('add-item').onclick=add;add();
    </script>
@endsection
