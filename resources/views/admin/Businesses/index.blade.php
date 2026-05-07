<h2 class="text-xl font-bold mb-4">Businesses</h2>

<table class="w-full bg-white shadow rounded">
    <thead>
        <tr class="bg-gray-100">
            <th class="p-2">Name</th>
            <th class="p-2">Type</th>
        </tr>
    </thead>
    <tbody>
        @foreach($businesses as $b)
        <tr>
            <td class="p-2">{{ $b->name }}</td>
            <td class="p-2">{{ $b->type }}</td>
        </tr>
        @endforeach
    </tbody>
</table>