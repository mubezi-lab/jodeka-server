<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Tables</title>

    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">

<div class="max-w-7xl mx-auto py-10 px-4">

    <h1 class="text-3xl font-bold mb-8">
        Database Tables
    </h1>

    @foreach($result as $tableName => $columns)

        <div class="bg-white rounded-2xl shadow mb-10 overflow-hidden">

            <div class="bg-gray-800 text-white px-6 py-4">
                <h2 class="text-xl font-semibold">
                    {{ $tableName }}
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-left">

                    <thead class="bg-gray-100 text-gray-700">

                        <tr>
                            <th class="px-4 py-3">Column</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Nullable</th>
                            <th class="px-4 py-3">Key</th>
                            <th class="px-4 py-3">Default</th>
                            <th class="px-4 py-3">Extra</th>
                        </tr>

                    </thead>

                    <tbody>

                        @foreach($columns as $column)

                            <tr class="border-t hover:bg-gray-50">

                                <td class="px-4 py-3 font-medium">
                                    {{ $column->COLUMN_NAME }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $column->COLUMN_TYPE }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $column->IS_NULLABLE }}
                                </td>

                                <td class="px-4 py-3">

                                    @if($column->COLUMN_KEY == 'PRI')
                                        <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">
                                            PRIMARY
                                        </span>
                                    @elseif($column->COLUMN_KEY == 'MUL')
                                        <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">
                                            INDEX
                                        </span>
                                    @else
                                        -
                                    @endif

                                </td>

                                <td class="px-4 py-3">
                                    {{ $column->COLUMN_DEFAULT ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $column->EXTRA ?: '-' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    @endforeach

</div>

</body>
</html>