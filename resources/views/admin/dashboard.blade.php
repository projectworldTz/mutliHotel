@extends('layouts.admin')
@section('title', __('Platform Dashboard'))
@section('page-title', __('Platform Dashboard'))

@section('content')

{{-- ── Auto-refresh indicator ──────────────────────────────────────────────── --}}
<div class="flex items-center justify-end mb-3 text-xs text-slate-400 dark:text-slate-500 gap-1.5">
    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
    <span>{{ __('Live data') }}</span>
    <span id="admin-refresh-countdown" class="tabular-nums">↻ 300s</span>
</div>

{{-- ── Top stat row ─────────────────────────────────────────────────────────── --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">

    {{-- Hotels --}}
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Total Hotels') }}</p>
            <svg class="h-5 w-5 text-navy dark:text-navy-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $platformStats['hotels']['total'] ?? 0 }}</p>
        <div class="mt-2 flex gap-3 text-xs">
            <span class="text-emerald-600 dark:text-emerald-400 font-medium">{{ $platformStats['hotels']['active'] ?? 0 }} {{ __('active') }}</span>
            <span class="text-amber-600 dark:text-amber-400">{{ $platformStats['hotels']['pending'] ?? 0 }} {{ __('pending') }}</span>
            <span class="text-rose-500">{{ $platformStats['hotels']['suspended'] ?? 0 }} {{ __('suspended') }}</span>
        </div>
    </div>

    {{-- Owners --}}
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Hotel Owners') }}</p>
            <svg class="h-5 w-5 text-navy dark:text-navy-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ $platformStats['total_owners'] }}</p>
        <p class="mt-2 text-xs text-slate-500">{{ $platformStats['total_users'] }} {{ __('total registered users') }}</p>
    </div>

    {{-- Bookings --}}
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Total Bookings') }}</p>
            <svg class="h-5 w-5 text-navy dark:text-navy-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ number_format($platformStats['total_bookings']) }}</p>
        <p class="mt-2 text-xs text-slate-500">{{ __('across all hotels') }}</p>
    </div>

    {{-- Revenue --}}
    <div class="stat-card">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('Platform Revenue') }}</p>
            <svg class="h-5 w-5 text-navy dark:text-navy-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ money($platformStats['total_revenue']) }}</p>
        <p class="mt-2 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
            {{ money($platformStats['revenue_month']) }} {{ __('this month') }}
        </p>
    </div>

</div>

{{-- ── Hotel status breakdown + Revenue chart ──────────────────────────────── --}}
<div class="grid gap-6 lg:grid-cols-3 mb-6">

    {{-- Revenue trend chart --}}
    <div class="lg:col-span-2 card p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Platform Revenue — Last 12 Months') }}</h3>
            <a href="{{ route('admin.reports.revenue') }}" class="btn-ghost btn-sm">{{ __('Full Report') }} →</a>
        </div>
        <div class="h-56">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Hotel status distribution --}}
    <div class="card p-5">
        <h3 class="font-bold text-slate-900 dark:text-white mb-4">{{ __('Hotel Status') }}</h3>
        <div class="space-y-3">
            @php
                $total = max(1, $platformStats['hotels']['total']);
                $statuses = [
                    ['label' => __('Active'),    'count' => $platformStats['hotels']['active']    ?? 0, 'color' => 'bg-emerald-500'],
                    ['label' => __('Pending'),   'count' => $platformStats['hotels']['pending']   ?? 0, 'color' => 'bg-amber-400'],
                    ['label' => __('Suspended'), 'count' => $platformStats['hotels']['suspended'] ?? 0, 'color' => 'bg-rose-500'],
                ];
            @endphp
            @foreach($statuses as $s)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-slate-600 dark:text-slate-300">{{ $s['label'] }}</span>
                    <span class="font-semibold text-slate-900 dark:text-white">{{ $s['count'] }}</span>
                </div>
                <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                    <div class="{{ $s['color'] }} h-full rounded-full transition-all"
                         style="width: {{ round($s['count'] / $total * 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700">
            <a href="{{ route('admin.hotels.index') }}" class="w-full btn-outline btn-sm text-center block">{{ __('Manage Hotels') }}</a>
        </div>
    </div>

</div>

{{-- ── Two-column lower panel ───────────────────────────────────────────────── --}}
<div class="grid gap-6 lg:grid-cols-2">

    {{-- Pending approvals --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Pending Approvals') }}</h3>
                @if($pendingHotels->isNotEmpty())
                    <span class="rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold px-2 py-0.5">
                        {{ $pendingHotels->count() }}
                    </span>
                @endif
            </div>
            <a href="{{ route('admin.hotels.index', ['status' => 'pending']) }}" class="btn-ghost btn-sm">{{ __('View All') }}</a>
        </div>

        @if($pendingHotels->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">
                <svg class="mx-auto h-10 w-10 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('All caught up — no pending hotels.') }}
            </div>
        @else
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($pendingHotels as $h)
            <div class="flex items-center justify-between px-5 py-3.5">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $h->name }}</p>
                    <p class="text-xs text-slate-500 truncate">
                        {{ $h->city }}, {{ $h->country }} &middot;
                        <span class="text-slate-400">{{ $h->owner->name ?? __('Unknown owner') }}</span>
                    </p>
                </div>
                <a href="{{ route('admin.hotels.show', $h) }}" class="ml-3 btn-primary btn-sm shrink-0">{{ __('Review') }}</a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Recent hotel registrations --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white">Recent Hotels</h3>
            <a href="{{ route('admin.hotels.index') }}" class="btn-ghost btn-sm">View All</a>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($recentHotels as $h)
            <div class="flex items-center justify-between px-5 py-3">
                <div class="flex items-center gap-3 min-w-0">
                    {{-- Status dot --}}
                    <span class="h-2 w-2 rounded-full shrink-0
                        {{ $h->status === 'active' ? 'bg-emerald-500' : ($h->status === 'suspended' ? 'bg-rose-500' : 'bg-amber-400') }}">
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $h->name }}</p>
                        <p class="text-xs text-slate-400">{{ $h->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.hotels.show', $h) }}" class="ml-3 btn-ghost btn-sm shrink-0">View</a>
            </div>
            @empty
            <p class="p-5 text-sm text-slate-400">No hotels registered yet.</p>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
// Auto-reload page every 5 minutes when tab is visible
(function () {
    let countdown = 300;
    const indicator = document.getElementById('admin-refresh-countdown');
    const tick = setInterval(() => {
        if (document.hidden) return;
        countdown--;
        if (indicator) indicator.textContent = '↻ ' + countdown + 's';
        if (countdown <= 0) { clearInterval(tick); window.location.reload(); }
    }, 1000);
})();

// Revenue chart
(function () {
    const revenue = @json($revenue);
    const ctx = document.getElementById('revenueChart');
    if (!ctx || !revenue.length) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: revenue.map(r => {
                const d = new Date(r.year, r.month - 1);
                return d.toLocaleString('default', { month: 'short', year: '2-digit' });
            }),
            datasets: [{
                label: 'Revenue ($)',
                data: revenue.map(r => r.total),
                backgroundColor: 'rgba(27, 58, 107, 0.1)',
                borderColor: '#1B3A6B',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#1B3A6B',
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '{{ config('app.currency') }} ' + v.toLocaleString() } }
            }
        }
    });
})();
</script>
@endpush
