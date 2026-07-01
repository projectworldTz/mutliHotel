@extends('layouts.app')
@section('title', __('My Dashboard'))

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="page-header">
        <h1 class="page-title">{{ __('Welcome, :name', ['name' => auth()->user()->name]) }}</h1>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            [__('Total Bookings'),   $stats['total']       ?? 0, 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'text-navy'],
            [__('Upcoming'),         $stats['upcoming']    ?? 0, 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'text-emerald-600'],
            [__('Completed'),        $stats['completed']   ?? 0, 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text-blue-600'],
            [__('Cancelled'),        $stats['cancelled']   ?? 0, 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'text-rose-500'],
        ] as [$label, $val, $icon, $color])
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
                <svg class="h-5 w-5 {{ $color }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </div>
            <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $val }}</p>
        </div>
        @endforeach
    </div>

    {{-- Recent bookings --}}
    <div class="mt-8 card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h2 class="font-bold text-slate-900 dark:text-white">{{ __('Recent Bookings') }}</h2>
            <a href="{{ route('account.bookings') }}" class="btn-ghost btn-sm">{{ __('View All') }} →</a>
        </div>
        @if($recentBookings->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <p class="text-slate-500">{{ __('No bookings yet.') }}</p>
                <a href="{{ route('hotels.index') }}" class="btn-primary mt-4">{{ __('Browse Rooms') }}</a>
            </div>
        @else
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Booking #') }}</th>
                        <th>{{ __('Hotel') }}</th>
                        <th>{{ __('Dates') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $b)
                    <tr class="tr-hover">
                        <td class="font-mono text-xs">{{ $b->booking_number }}</td>
                        <td class="font-medium">{{ $b->hotel->name ?? 'N/A' }}</td>
                        <td class="text-slate-500 text-sm whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($b->check_in)->format('d M') }} –
                            {{ \Carbon\Carbon::parse($b->check_out)->format('d M Y') }}
                        </td>
                        <td class="font-semibold">{{ money($b->grand_total ?? 0) }}</td>
                        <td><span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td>
                        <td>
                            <a href="{{ route('booking.show', $b->booking_number) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
