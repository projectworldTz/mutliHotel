@extends('layouts.receptionist')
@section('title', 'Dashboard')
@section('page-title', 'Reception Dashboard')

@section('content')
{{-- Quick stats --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Arrivals Today</p>
        <p class="mt-1 text-3xl font-bold text-navy dark:text-navy-light">{{ $stats['arrivals_today'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Departures Today</p>
        <p class="mt-1 text-3xl font-bold text-gold">{{ $stats['departures_today'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Awaiting Confirm</p>
        <p class="mt-1 text-3xl font-bold text-amber-500">{{ $stats['pending_confirm'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Currently In-House</p>
        <p class="mt-1 text-3xl font-bold text-emerald-600">{{ $stats['currently_in'] }}</p>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    {{-- Today's arrivals --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h2 class="font-bold text-slate-900 dark:text-white">Today's Arrivals</h2>
            <a href="{{ route('receptionist.bookings.index', ['status' => 'confirmed']) }}" class="text-xs text-navy dark:text-navy-light hover:underline">View all</a>
        </div>
        @forelse($arrivalsToday as $booking)
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-50 dark:border-slate-800 last:border-0">
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->user->name }}</p>
                <p class="text-xs text-slate-500">{{ $booking->booking_number }} · {{ $booking->roomType->name ?? '—' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-{{ $booking->status }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                @if($booking->status === 'confirmed')
                <form method="POST" action="{{ route('receptionist.bookings.check-in', $booking) }}">
                    @csrf
                    <button class="btn-primary btn-sm">Check In</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <p class="p-5 text-sm text-slate-500">No arrivals scheduled today.</p>
        @endforelse
    </div>

    {{-- Today's departures --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h2 class="font-bold text-slate-900 dark:text-white">Today's Departures</h2>
            <a href="{{ route('receptionist.bookings.index', ['status' => 'checked_in']) }}" class="text-xs text-navy dark:text-navy-light hover:underline">View all</a>
        </div>
        @forelse($departuresToday as $booking)
        <div class="flex items-center justify-between px-5 py-3 border-b border-slate-50 dark:border-slate-800 last:border-0">
            <div>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $booking->user->name }}</p>
                <p class="text-xs text-slate-500">{{ $booking->booking_number }} · Room {{ $booking->roomType->name ?? '—' }}</p>
            </div>
            <form method="POST" action="{{ route('receptionist.bookings.check-out', $booking) }}">
                @csrf
                <button class="btn-outline btn-sm">Check Out</button>
            </form>
        </div>
        @empty
        <p class="p-5 text-sm text-slate-500">No departures scheduled today.</p>
        @endforelse
    </div>
</div>
@endsection
