@extends('layouts.admin')

@section('title', 'Bagambakamo Reports')

@section('content')

    <div class="container">

        <div class="page-header">

            <div>

                <h2>
                    <i class="fa-solid fa-chart-column"></i>
                    Bagambakamo Reports
                </h2>

                <p class="text-gray-500">
                    Member financial summary
                </p>

            </div>


            <a href="{{ route('bagambakamo.report.pdf') }}" target="_blank" class="btn success">

                <i class="fa-solid fa-file-pdf"></i>
                Download PDF

            </a>

        </div>


        <div class="table-wrapper">

            <table class="table">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Member</th>
                        <th>Phone</th>
                        <th>Expected</th>
                        <th>Events</th>
                        <th>Total Paid</th>
                        <th>Balance</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($members as $index => $member)

                                <tr>

                                    <td>
                                        {{ $index + 1 }}
                                    </td>

                                    <td>
                                        {{ $member->full_name }}
                                    </td>

                                    <td>
                                        {{ $member->phone ?: '-' }}
                                    </td>

                                    <td>
                                        TSH
                                        {{ number_format($member->expected_amount) }}
                                    </td>

                                    <td>
                                        TSH
                                        {{ number_format($member->total_events) }}
                                    </td>

                                    <td class="text-success">

                                        TSH
                                        {{ number_format($member->total_paid) }}

                                    </td>

                                    <td class="{{
                        $member->balance_amount > 0
                        ? 'text-danger'
                        : 'text-success'
                                            }}">

                                        TSH
                                        {{ number_format($member->balance_amount) }}

                                    </td>

                                </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="text-center">

                                No report data found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

@endsection