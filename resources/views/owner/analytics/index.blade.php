@extends('layouts.owner')
@section('title', __('Analytics') . ' — ' . $hotel->name)
@section('page-title', __('Advanced Analytics'))

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- ── Header: hotel name + period selector ────────────────────────────────── --}}
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $hotel->name }}</p>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Performance Analytics') }}</h2>
    </div>
    <div class="flex items-center gap-2 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-1 shadow-sm">
        @foreach([30 => __('Last 30 Days'), 90 => __('Last 3 Months'), 365 => __('Last 12 Months')] as $p => $label)
        <a href="{{ route('owner.analytics.index', ['hotel' => $hotel, 'period' => $p]) }}"
           class="rounded-lg px-3 py-1.5 text-sm font-medium transition
                  {{ $period == $p
                      ? 'bg-navy text-white dark:bg-amber-500 dark:text-slate-900'
                      : 'text-slate-500 hover:text-slate-900 dark:hover:text-white' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>
</div>

{{-- ── KPI Cards ────────────────────────────────────────────────────────────── --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 mb-6">
    @foreach($kpis as $kpi)
    @php
        $up   = $kpi['change'] !== null && $kpi['change'] >= 0;
        $down = $kpi['change'] !== null && $kpi['change'] < 0;
        $colorMap = [
            'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400'],
            'blue'    => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',   'text' => 'text-blue-600 dark:text-blue-400'],
            'indigo'  => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20','text' => 'text-indigo-600 dark:text-indigo-400'],
            'purple'  => ['bg' => 'bg-purple-50 dark:bg-purple-900/20','text' => 'text-purple-600 dark:text-purple-400'],
            'amber'   => ['bg' => 'bg-amber-50 dark:bg-amber-900/20',  'text' => 'text-amber-600 dark:text-amber-400'],
            'rose'    => ['bg' => 'bg-rose-50 dark:bg-rose-900/20',    'text' => 'text-rose-600 dark:text-rose-400'],
            'slate'   => ['bg' => 'bg-slate-100 dark:bg-slate-700',    'text' => 'text-slate-600 dark:text-slate-300'],
        ];
        $c = $colorMap[$kpi['color']];
    @endphp
    <div class="min-w-0 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4 shadow-sm">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 truncate">{{ $kpi['label'] }}</p>
        <p class="text-xl lg:text-2xl font-bold text-slate-900 dark:text-white leading-tight break-words">
            {{ $kpi['prefix'] }}{{ $kpi['value'] }}{{ $kpi['suffix'] }}
        </p>
        @if($kpi['change'] !== null)
        <p class="mt-1.5 flex items-center gap-1 text-xs font-semibold {{ $up ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' }}">
            @if($up)
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
            @else
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            @endif
            {{ abs($kpi['change']) }}% {{ __('vs prev. period') }}
        </p>
        @else
        <p class="mt-1.5 text-xs text-slate-400">{{ __('No previous data') }}</p>
        @endif
    </div>
    @endforeach
</div>

{{-- ── Revenue + Bookings trend charts ─────────────────────────────────────── --}}
<div class="grid gap-6 lg:grid-cols-3 mb-6">

    {{-- Revenue trend (2/3 width) --}}
    <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">{{ __('Revenue Trend') }}</h3>
        <div class="relative h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Booking status doughnut (1/3 width) --}}
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">{{ __('Booking Status') }}</h3>
        <div class="relative h-48">
            <canvas id="statusChart"></canvas>
        </div>
        <div class="mt-3 space-y-1">
            @php
                $statusLabels = ['confirmed'=>'Confirmed','checked_in'=>'Checked In','checked_out'=>'Checked Out','cancelled'=>'Cancelled','pending'=>'Pending','no_show'=>'No Show'];
                $statusColors = ['confirmed'=>'#3b82f6','checked_in'=>'#10b981','checked_out'=>'#6b7280','cancelled'=>'#ef4444','pending'=>'#f59e0b','no_show'=>'#8b5cf6'];
            @endphp
            @foreach($statusBreakdown as $status => $count)
            <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300">
                    <span class="h-2 w-2 rounded-full inline-block" style="background:{{ $statusColors[$status] ?? '#94a3b8' }}"></span>
                    {{ $statusLabels[$status] ?? ucfirst($status) }}
                </span>
                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── Visits trend ─────────────────────────────────────────────────────────── --}}
<div class="grid gap-6 lg:grid-cols-3 mb-6">

    {{-- Summary card --}}
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5 flex flex-col justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-1">{{ __('Page Visits') }}</h3>
            <p class="text-3xl font-bold leading-none text-indigo-600 dark:text-indigo-400">{{ number_format($visitsTrend->sum()) }}</p>
            <p class="text-xs text-slate-400 mt-1.5">{{ __('total in period') }}</p>
        </div>
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Daily average') }}</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $visitsTrend->count() ? round($visitsTrend->avg(), 1) : 0 }}</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Peak day') }}</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white">{{ $visitsTrend->max() ?: 0 }}</p>
            </div>
        </div>
    </div>

    {{-- Visits per day chart (2/3 width) --}}
    <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">{{ __('Visits Per Day') }}</h3>
        <div class="relative h-48">
            <canvas id="visitsChart"></canvas>
        </div>
    </div>
</div>

{{-- ── Visits variation (day-over-day growth) ──────────────────────────────── --}}
<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5 mb-6">
    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">{{ __('Visits Growth (Day-over-Day)') }}</h3>
    <div class="relative h-48">
        <canvas id="visitsVariationChart"></canvas>
    </div>
</div>

{{-- ── Room type performance + busy days ───────────────────────────────────── --}}
<div class="grid gap-6 lg:grid-cols-3 mb-6">

    {{-- Room type table (2/3 width) --}}
    <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Room Type Performance') }}</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Room Type') }}</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Bookings') }}</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Occupancy') }}</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('ADR') }}</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('Revenue') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($roomTypePerf as $rt)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                    <td class="px-4 py-3 font-medium text-slate-900 dark:text-white">
                        {{ $rt['name'] }}
                        <span class="ml-1 text-xs text-slate-400">({{ $rt['rooms'] }} {{ __('rooms') }})</span>
                    </td>
                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ $rt['bookings'] }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <div class="w-16 bg-slate-200 dark:bg-slate-600 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $rt['occupancy'] >= 70 ? 'bg-emerald-500' : ($rt['occupancy'] >= 40 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                     style="width:{{ min($rt['occupancy'], 100) }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-200 w-10 text-right">{{ $rt['occupancy'] }}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">{{ money($rt['adr']) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-900 dark:text-white">{{ money($rt['revenue']) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">{{ __('No booking data for this period.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Busiest days + cancel rate (1/3 width) --}}
    <div class="space-y-4">

        {{-- Cancellation rate card --}}
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">{{ __('Cancellation Rate') }}</h3>
            <div class="flex items-end gap-3">
                <p class="text-4xl font-bold {{ $cancelRate > 20 ? 'text-rose-500' : ($cancelRate > 10 ? 'text-amber-500' : 'text-emerald-500') }}">
                    {{ $cancelRate }}%
                </p>
                <p class="text-xs text-slate-400 mb-1 leading-snug">{{ __('of all') }}<br>{{ __('bookings') }}</p>
            </div>
            <div class="mt-2 w-full bg-slate-200 dark:bg-slate-600 rounded-full h-2">
                <div class="h-2 rounded-full {{ $cancelRate > 20 ? 'bg-rose-500' : ($cancelRate > 10 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                     style="width:{{ min($cancelRate, 100) }}%"></div>
            </div>
        </div>

        {{-- Busiest days card --}}
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">{{ __('Busiest Check-in Days') }}</h3>
            @php $maxDay = $busyDays->max() ?: 1; @endphp
            <div class="space-y-2">
                @forelse($busyDays as $day => $count)
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-20 text-slate-500 dark:text-slate-400 shrink-0">{{ $day }}</span>
                    <div class="flex-1 bg-slate-200 dark:bg-slate-600 rounded-full h-2">
                        <div class="h-2 rounded-full bg-navy dark:bg-amber-400"
                             style="width:{{ round(($count / $maxDay) * 100) }}%"></div>
                    </div>
                    <span class="w-6 text-right font-semibold text-slate-700 dark:text-slate-200">{{ $count }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400">{{ __('No data available.') }}</p>
                @endforelse
            </div>
        </div>

    </div>
</div>

{{-- ── Bookings volume bar chart ────────────────────────────────────────────── --}}
<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-4">{{ __('Bookings Volume') }}</h3>
    <div class="relative h-48">
        <canvas id="bookingsChart"></canvas>
    </div>
</div>

@endsection

@push('scripts')
<script>
const isDark = document.documentElement.classList.contains('dark');
const gridColor  = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
const labelColor = isDark ? '#94a3b8' : '#64748b';

const trendLabels = @json($trendLabels->values());
const trendData   = @json($trendData->values());
const bookingsData = @json($bookingsTrend->values());
const visitsData   = @json($visitsTrend->values());
const visitsVariationData = @json($visitsVariation->values());

// ── Revenue chart ─────────────────────────────────────────────────────────────
new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Revenue',
            data: trendData,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16,185,129,0.1)',
            borderWidth: 2.5,
            pointRadius: trendLabels.length > 30 ? 0 : 3,
            pointHoverRadius: 5,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { color: labelColor, maxTicksLimit: 8, maxRotation: 0 } },
            y: { grid: { color: gridColor }, ticks: { color: labelColor, callback: v => '{{ config('app.currency') }} ' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v) } }
        }
    }
});

// ── Status doughnut ───────────────────────────────────────────────────────────
@php
    $statusKeys   = array_keys($statusBreakdown->toArray());
    $statusVals   = array_values($statusBreakdown->toArray());
    $statusColMap = ['confirmed'=>'#3b82f6','checked_in'=>'#10b981','checked_out'=>'#6b7280','cancelled'=>'#ef4444','pending'=>'#f59e0b','no_show'=>'#8b5cf6'];
    $statusBg     = array_map(fn($s) => $statusColMap[$s] ?? '#94a3b8', $statusKeys);
    $statusLbls   = array_map(fn($s) => ucwords(str_replace('_',' ',$s)), $statusKeys);
@endphp
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: @json($statusLbls),
        datasets: [{ data: @json($statusVals), backgroundColor: @json($statusBg), borderWidth: 0, hoverOffset: 6 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        cutout: '72%',
    }
});

// ── Bookings volume bar ───────────────────────────────────────────────────────
new Chart(document.getElementById('bookingsChart'), {
    type: 'bar',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Bookings',
            data: bookingsData,
            backgroundColor: 'rgba(99,102,241,0.7)',
            borderRadius: 4,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: labelColor, maxTicksLimit: 8, maxRotation: 0 } },
            y: { grid: { color: gridColor }, ticks: { color: labelColor, precision: 0 } }
        }
    }
});

// ── Visits per day (gradient area) ─────────────────────────────────────────────
const visitsCtx = document.getElementById('visitsChart').getContext('2d');
const visitsGradient = visitsCtx.createLinearGradient(0, 0, 0, 200);
visitsGradient.addColorStop(0, 'rgba(99,102,241,0.35)');
visitsGradient.addColorStop(1, 'rgba(99,102,241,0)');

new Chart(visitsCtx, {
    type: 'line',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Visits',
            data: visitsData,
            borderColor: '#6366f1',
            backgroundColor: visitsGradient,
            borderWidth: 2.5,
            pointRadius: trendLabels.length > 30 ? 0 : 3,
            pointBackgroundColor: '#6366f1',
            pointHoverRadius: 5,
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: labelColor, maxTicksLimit: 8, maxRotation: 0 } },
            y: { grid: { color: gridColor }, ticks: { color: labelColor, precision: 0 } }
        }
    }
});

// ── Visits growth (day-over-day %) ──────────────────────────────────────────────
const variationColors = visitsVariationData.map(v => v === null ? gridColor : (v >= 0 ? '#10b981' : '#ef4444'));

new Chart(document.getElementById('visitsVariationChart'), {
    type: 'bar',
    data: {
        labels: trendLabels,
        datasets: [{
            label: 'Growth %',
            data: visitsVariationData,
            backgroundColor: variationColors,
            borderRadius: 4,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.raw === null ? 'No prior data' : ctx.raw + '%'
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: labelColor, maxTicksLimit: 8, maxRotation: 0 } },
            y: { grid: { color: gridColor }, ticks: { color: labelColor, callback: v => v + '%' } }
        }
    }
});
</script>
@endpush
