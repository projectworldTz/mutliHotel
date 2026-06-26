@extends('layouts.owner')
@section('title', 'Owner Dashboard')
@section('page-title', 'Owner Dashboard')

@section('content')
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    @foreach([
        ['My Hotels',     $stats['hotels']    ?? 0, 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['Active Bookings',$stats['active_bookings'] ?? 0, 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['Month Revenue',  '$' . number_format($stats['revenue_month'] ?? 0, 0), 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Total Rooms',    $stats['rooms']     ?? 0, 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
    ] as [$label, $val, $icon])
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <svg class="h-5 w-5 text-navy dark:text-navy-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
            </svg>
        </div>
        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $val }}</p>
    </div>
    @endforeach
</div>

{{-- Hotels quick list --}}
<div class="grid gap-6 lg:grid-cols-2">
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white">My Hotels</h3>
            <a href="{{ route('owner.hotels.create') }}" class="btn-primary btn-sm">+ Add Hotel</a>
        </div>
        @if($hotels->isEmpty())
            <p class="p-5 text-sm text-slate-500">You haven't listed any hotels yet.</p>
        @else
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($hotels as $h)
            <div class="flex items-center justify-between px-5 py-3">
                <div>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $h->name }}</p>
                    <p class="text-xs text-slate-500">{{ $h->city }} · <span class="badge badge-{{ $h->status === 'active' ? 'active' : ($h->status === 'suspended' ? 'suspended' : 'pending-hotel') }} text-[10px]">{{ $h->status }}</span></p>
                </div>
                <a href="{{ route('owner.hotels.show', $h) }}" class="btn-ghost btn-sm">Manage</a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Recent bookings --}}
    <div class="card">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white">Recent Bookings</h3>
        </div>
        @if($recentBookings->isEmpty())
            <p class="p-5 text-sm text-slate-500">No recent bookings.</p>
        @else
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($recentBookings as $b)
            <div class="flex items-center justify-between px-5 py-3">
                <div>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">#{{ $b->booking_number }}</p>
                    <p class="text-xs text-slate-500">{{ $b->user->name ?? 'Guest' }} · {{ $b->hotel->name ?? '' }}</p>
                </div>
                <div class="text-right">
                    <span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span>
                    <p class="text-xs text-slate-500 mt-0.5">${{ number_format($b->grand_total ?? 0, 0) }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
