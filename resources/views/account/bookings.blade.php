@extends('layouts.app')
@section('title', 'My Bookings')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="page-header">
        <h1 class="page-title">My Bookings</h1>
    </div>

    @if($bookings->isEmpty())
        <div class="card flex flex-col items-center justify-center py-20 text-center">
            <svg class="h-14 w-14 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">No bookings found</h3>
            <p class="mt-1 text-sm text-slate-500">Start by browsing our hotels.</p>
            <a href="{{ route('hotels.index') }}" class="btn-primary mt-5">Browse Hotels</a>
        </div>
    @else
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Hotel</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $b)
                <tr class="tr-hover">
                    <td class="font-mono text-xs font-medium text-navy dark:text-navy-light">{{ $b->booking_number }}</td>
                    <td>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $b->hotel->name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-500">{{ $b->hotel->city ?? '' }}</p>
                    </td>
                    <td class="whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($b->check_in)->format('d M Y') }}</td>
                    <td class="whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($b->check_out)->format('d M Y') }}</td>
                    <td class="font-semibold">${{ number_format($b->total_amount ?? 0, 2) }}</td>
                    <td><span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td>
                    <td>
                        <a href="{{ route('booking.show', $b->booking_number) }}" class="btn-ghost btn-sm">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $bookings->links() }}</div>
    @endif
</div>
@endsection
