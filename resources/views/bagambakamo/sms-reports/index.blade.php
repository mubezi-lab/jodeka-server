@extends('layouts.admin')

@section('title', 'Bagambakamo SMS Reports')

@section('content')

    <div class="container">

        <div class="page-header">

            <div>

                <h2>
                    <i class="fa-solid fa-comments"></i>
                    SMS Reports
                </h2>

                <p class="text-gray-500">
                    Bagambakamo SMS history
                </p>

            </div>

        </div>


        <div class="table-wrapper">

            <table class="table">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Time</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($reports as $report)

                                <tr>

                                    <td>
                                        {{ $report->id }}
                                    </td>

                                    <td>
                                        {{ $report->name ?: '-' }}
                                    </td>

                                    <td>
                                        {{ $report->phone }}
                                    </td>

                                    <td>
                                        {{ $report->message }}
                                    </td>

                                    <td>
                                        {{ $report->group_type ?? '-' }}
                                    </td>

                                    <td>

                                        <span class="{{
                        $report->status === 'sent'
                        ? 'text-success'
                        : 'text-danger'
                                                }}">

                                            {{ ucfirst($report->status) }}

                                        </span>

                                    </td>

                                    <td>

                                        @if($report->sent_at)

                                                        {{
                                            $report->sent_at
                                                ->format('d/m/Y H:i')
                                                                }}

                                        @else

                                            -

                                        @endif

                                    </td>

                                </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="text-center">

                                No SMS reports found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="mt-4">
            {{ $reports->links() }}
        </div>

    </div>

@endsection