@extends('layouts.admin')
@section('title', $hotel->name . ' — ' . __('Hotel Management'))
@section('page-title', $hotel->name)

@php $tab = $activeTab ?? 'overview'; @endphp

@section('content')

{{-- ── Breadcrumb ───────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-5">
    <a href="{{ route('admin.hotels.index') }}" class="hover:text-navy dark:hover:text-amber-400 transition">{{ __('Hotels') }}</a>
    <span>/</span>
    <span class="text-slate-900 dark:text-white font-medium">{{ $hotel->name }}</span>
    @if($tab !== 'overview')
        <span>/</span>
        <span class="text-slate-900 dark:text-white font-medium capitalize">{{ $tab }}</span>
    @endif
</div>

{{-- ── Hotel header ─────────────────────────────────────────────────────────── --}}
<div class="card p-5 mb-5">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            {{-- Status dot --}}
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
                {{ $hotel->status === 'active' ? 'bg-emerald-100 dark:bg-emerald-900/30' : ($hotel->status === 'suspended' ? 'bg-rose-100 dark:bg-rose-900/30' : 'bg-amber-100 dark:bg-amber-900/30') }}">
                <span class="h-3 w-3 rounded-full
                    {{ $hotel->status === 'active' ? 'bg-emerald-500' : ($hotel->status === 'suspended' ? 'bg-rose-500' : 'bg-amber-400') }}">
                </span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-900 dark:text-white leading-tight">{{ $hotel->name }}</h1>
                <p class="text-sm text-slate-500">
                    {{ $hotel->city }}, {{ $hotel->country }}
                    &middot; {{ __('Owner') }}: <span class="font-medium text-slate-700 dark:text-slate-300">{{ $hotel->owner->name ?? 'N/A' }}</span>
                    @if($hotel->is_featured)
                        &middot; <span class="text-amber-500 font-medium">★ {{ __('Featured') }}</span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="flex flex-wrap gap-2">
            @if($hotel->status === 'pending')
            <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}" data-loading>
                @csrf
                <button type="submit" class="btn-success btn-sm">{{ __('Approve') }}</button>
            </form>
            @endif

            @if($hotel->status === 'active')
            <form method="POST" action="{{ route('admin.hotels.suspend', $hotel) }}"
                  data-loading data-confirm="{{ __('Suspend this hotel?') }}">
                @csrf
                <button type="submit" class="btn-danger btn-sm">{{ __('Suspend') }}</button>
            </form>
            @endif

            @if($hotel->status === 'suspended')
            <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}" data-loading>
                @csrf
                <button type="submit" class="btn-success btn-sm">{{ __('Reactivate') }}</button>
            </form>
            @endif

            <form method="POST" action="{{ route('admin.hotels.featured', $hotel) }}" data-loading>
                @csrf
                <button type="submit" class="btn-outline btn-sm">
                    {{ $hotel->is_featured ? __('Unfeature') : __('Feature') }}
                </button>
            </form>

            {{-- Impersonate owner --}}
            @if($hotel->owner)
            <form method="POST" action="{{ route('admin.impersonate.start', $hotel->owner) }}">
                @csrf
                <button class="btn-sm border border-amber-400 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                    👁 {{ __('View as Owner') }}
                </button>
            </form>
            @endif

            <a href="{{ route('hotels.show', $hotel) }}" target="_blank"
               class="btn-sm border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                {{ __('Public Page') }} ↗
            </a>
        </div>
    </div>
</div>

{{-- ── Tab navigation ───────────────────────────────────────────────────────── --}}
<div class="border-b border-slate-200 dark:border-slate-700 mb-6 -mx-1">
    <nav class="flex gap-1 overflow-x-auto px-1">
        @php
            $tabs = [
                ['id' => 'overview',  'label' => __('Overview'),  'route' => route('admin.hotels.show', $hotel)],
                ['id' => 'bookings',  'label' => __('Bookings'),  'route' => route('admin.hotels.hub.bookings', $hotel)],
                ['id' => 'revenue',   'label' => __('Revenue'),   'route' => route('admin.hotels.hub.revenue', $hotel)],
                ['id' => 'rooms',     'label' => __('Rooms'),     'route' => route('admin.hotels.hub.rooms', $hotel)],
                ['id' => 'staff',     'label' => __('Staff'),     'route' => route('admin.hotels.hub.staff', $hotel)],
                ['id' => 'guests',    'label' => __('Guests'),    'route' => route('admin.hotels.hub.guests', $hotel)],
                ['id' => 'features',  'label' => '★ ' . __('Features'), 'route' => route('admin.hotels.hub.features', $hotel)],
            ];
        @endphp
        @foreach($tabs as $t)
        <a href="{{ $t['route'] }}"
           class="whitespace-nowrap px-4 py-3 text-sm font-medium border-b-2 -mb-px transition
               {{ $tab === $t['id']
                   ? 'border-navy dark:border-amber-400 text-navy dark:text-amber-400'
                   : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:border-slate-300' }}">
            {{ $t['label'] }}
        </a>
        @endforeach
    </nav>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     TAB: OVERVIEW
══════════════════════════════════════════════════════════════════════════════ --}}
@if($tab === 'overview')
<div class="space-y-6">

    {{-- Stats row --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            [__('Total Bookings'),  $stats['total']        ?? 0,  'text-slate-900 dark:text-white', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            [__('Active'),          ($stats['pending']??0) + ($stats['confirmed']??0) + ($stats['checked_in']??0), 'text-emerald-600 dark:text-emerald-400', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            [__('This Month'),      money($stats['revenue_month'] ?? 0), 'text-navy dark:text-amber-400', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            [__('Total Revenue'),   money($stats['total_revenue'] ?? 0), 'text-navy dark:text-amber-400', 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z'],
        ] as [$label, $val, $valClass, $icon])
        <div class="stat-card">
            <div class="flex items-center justify-between">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </div>
            <p class="mt-2 text-2xl font-bold {{ $valClass }}">{{ $val }}</p>
        </div>
        @endforeach
    </div>

    {{-- Revenue chart + hotel details side-by-side --}}
    <div class="grid gap-6 lg:grid-cols-3">

        <div class="lg:col-span-2 card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Revenue (Last 12 Months)') }}</h3>
                <a href="{{ route('admin.hotels.hub.revenue', $hotel) }}" class="btn-ghost btn-sm">{{ __('Full Report') }} →</a>
            </div>
            <div class="h-52">
                <canvas id="hotelRevenueChart"></canvas>
            </div>
        </div>

        <div class="card p-5 text-sm space-y-2.5">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">{{ __('Hotel Details') }}</h3>
            @foreach([
                [__('Star Rating'), ($hotel->star_rating ?? '—') . '★'],
                [__('Category'),    $hotel->category->name ?? '—'],
                [__('Rooms'),       ($stats['total_rooms'] ?? 0) . ' ' . __('total')],
                [__('Room Types'),  $hotel->roomTypes->count()],
                [__('Check-in'),    $hotel->check_in_time  ?? '14:00'],
                [__('Check-out'),   $hotel->check_out_time ?? '11:00'],
                [__('Phone'),       $hotel->phone ?? '—'],
                [__('Email'),       $hotel->email ?? '—'],
                [__('Listed'),      $hotel->created_at->format('d M Y')],
            ] as [$k, $v])
            <div class="flex justify-between gap-4">
                <span class="text-slate-500 dark:text-slate-400 shrink-0">{{ $k }}</span>
                <span class="text-slate-900 dark:text-white text-right">{{ $v }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent bookings for this hotel --}}
    @if(isset($recentBookings) && $recentBookings->isNotEmpty())
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Recent Bookings') }}</h3>
            <a href="{{ route('admin.hotels.hub.bookings', $hotel) }}" class="btn-ghost btn-sm">{{ __('View All') }} →</a>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>{{ __('Booking #') }}</th><th>{{ __('Guest') }}</th><th>{{ __('Dates') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th></tr></thead>
                <tbody>
                    @foreach($recentBookings as $b)
                    <tr class="tr-hover">
                        <td class="font-mono text-xs">{{ $b->booking_number }}</td>
                        <td>{{ $b->user->name ?? 'N/A' }}</td>
                        <td class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($b->check_in)->format('d M') }} → {{ \Carbon\Carbon::parse($b->check_out)->format('d M Y') }}</td>
                        <td class="font-semibold">{{ money($b->grand_total) }}</td>
                        <td><span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Room types summary --}}
    @if($hotel->roomTypes->isNotEmpty())
    <div class="card">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Room Types') }} ({{ $hotel->roomTypes->count() }})</h3>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Beds') }}</th><th>{{ __('Max Guests') }}</th><th>{{ __('Base Price') }}</th><th>{{ __('Rooms') }}</th></tr></thead>
                <tbody>
                    @foreach($hotel->roomTypes as $rt)
                    <tr class="tr-hover">
                        <td class="font-medium">{{ $rt->name }}</td>
                        <td>{{ $rt->beds_count }}× {{ $rt->bed_type }}</td>
                        <td>{{ $rt->max_guests }}</td>
                        <td>{{ money($rt->base_price) }}</td>
                        <td>{{ $rt->rooms->count() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
(function () {
    const revenue = @json($revenue ?? []);
    const ctx = document.getElementById('hotelRevenueChart');
    if (!ctx || !revenue.length) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: revenue.map(r => {
                const d = new Date(r.year, r.month - 1);
                return d.toLocaleString('default', { month: 'short', year: '2-digit' });
            }),
            datasets: [{
                label: 'Revenue ({{ config('app.currency') }})',
                data: revenue.map(r => r.total),
                backgroundColor: 'rgba(27, 58, 107, 0.75)',
                borderColor: '#1B3A6B',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '{{ config('app.currency') }} ' + v.toLocaleString() } } }
        }
    });
})();
</script>
@endpush

{{-- ══════════════════════════════════════════════════════════════════════════════
     TAB: BOOKINGS
══════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tab === 'bookings')
{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <select name="status" class="form-input w-auto text-sm">
        <option value="">{{ __('All Statuses') }}</option>
        @foreach(['pending','confirmed','checked_in','checked_out','cancelled'] as $s)
        <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
        @endforeach
    </select>
    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-input w-auto text-sm" placeholder="{{ __('From') }}">
    <input type="date" name="date_to"   value="{{ $filters['date_to']   ?? '' }}" class="form-input w-auto text-sm" placeholder="{{ __('To') }}">
    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Booking # or guest name…') }}" class="form-input flex-1 min-w-48 text-sm">
    <button type="submit" class="btn-primary btn-sm">{{ __('Filter') }}</button>
    @if(array_filter($filters ?? []))
    <a href="{{ route('admin.hotels.hub.bookings', $hotel) }}" class="btn-ghost btn-sm">{{ __('Clear') }}</a>
    @endif
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>{{ __('Booking #') }}</th><th>{{ __('Guest') }}</th><th>{{ __('Check-in') }}</th><th>{{ __('Check-out') }}</th><th>{{ __('Nights') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Status') }}</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr class="tr-hover">
                    <td class="font-mono text-xs">{{ $b->booking_number }}</td>
                    <td>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $b->user->name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-400">{{ $b->user->email ?? '' }}</p>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($b->check_in)->format('d M Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($b->check_out)->format('d M Y') }}</td>
                    <td class="text-center">{{ $b->nights }}</td>
                    <td class="font-semibold">{{ money($b->grand_total) }}</td>
                    <td><span class="badge badge-{{ $b->status }}">{{ ucfirst(str_replace('_',' ',$b->status)) }}</span></td>
                    <td><a href="{{ route('admin.bookings.show', $b) }}" class="btn-ghost btn-sm">{{ __('View') }}</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-10 text-center text-slate-400">{{ __('No bookings found for this hotel.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($bookings) && $bookings->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $bookings->links() }}</div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     TAB: REVENUE
══════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tab === 'revenue')
<div class="space-y-6">

    {{-- Controls --}}
    <form method="GET" class="flex items-center gap-3">
        <label class="text-sm text-slate-600 dark:text-slate-400">{{ __('Period') }}:</label>
        <select name="months" class="form-input w-auto text-sm" onchange="this.form.submit()">
            @foreach([3 => '3 months', 6 => '6 months', 12 => '12 months', 24 => '24 months'] as $v => $l)
            <option value="{{ $v }}" @selected(($months ?? 12) == $v)>{{ $l }}</option>
            @endforeach
        </select>
    </form>

    {{-- Summary stats --}}
    <div class="grid gap-4 sm:grid-cols-3">
        @foreach([
            [__('Total Revenue'),  money($stats['total_revenue'] ?? 0)],
            [__('Total Bookings'), $stats['total'] ?? 0],
            [__('Cancelled'),      $stats['cancelled'] ?? 0],
        ] as [$label, $val])
        <div class="stat-card">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900 dark:text-white">{{ $val }}</p>
        </div>
        @endforeach
    </div>

    {{-- Revenue chart --}}
    <div class="card p-5">
        <h3 class="font-bold text-slate-900 dark:text-white mb-4">{{ __('Monthly Revenue') }}</h3>
        <div class="h-64"><canvas id="hotelRevenueChart"></canvas></div>
    </div>

    {{-- Monthly table --}}
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>{{ __('Month') }}</th><th class="text-right">{{ __('Bookings') }}</th><th class="text-right">{{ __('Revenue') }}</th></tr></thead>
                <tbody>
                    @forelse(array_reverse($revenue ?? []) as $r)
                    <tr class="tr-hover">
                        <td>{{ date('F Y', mktime(0,0,0,$r['month'],1,$r['year'])) }}</td>
                        <td class="text-right">{{ $r['bookings'] ?? '—' }}</td>
                        <td class="text-right font-semibold">{{ money($r['total']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-8 text-center text-slate-400">{{ __('No revenue data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const revenue = @json($revenue ?? []);
    const ctx = document.getElementById('hotelRevenueChart');
    if (!ctx || !revenue.length) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: revenue.map(r => {
                const d = new Date(r.year, r.month - 1);
                return d.toLocaleString('default', { month: 'short', year: '2-digit' });
            }),
            datasets: [{
                label: 'Revenue ({{ config('app.currency') }})',
                data: revenue.map(r => r.total),
                backgroundColor: 'rgba(27, 58, 107, 0.75)',
                borderColor: '#1B3A6B', borderWidth: 1, borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '{{ config('app.currency') }} ' + v.toLocaleString() } } }
        }
    });
})();
</script>
@endpush

{{-- ══════════════════════════════════════════════════════════════════════════════
     TAB: ROOMS
══════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tab === 'rooms')
<div class="space-y-5">
    @forelse($hotel->roomTypes as $rt)
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3">
                @if($rt->images->isNotEmpty())
                <img src="{{ $rt->images->first()->url }}" alt="{{ $rt->name }}"
                     class="h-10 w-14 rounded-lg object-cover">
                @endif
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $rt->name }}</h3>
                    <p class="text-xs text-slate-500">
                        {{ $rt->beds_count }}× {{ $rt->bed_type }} · {{ __('Max') }} {{ $rt->max_guests }} {{ __('guests') }} · {{ money($rt->base_price) }}/{{ __('night') }}
                    </p>
                </div>
            </div>
            <span class="badge badge-confirmed">{{ $rt->rooms->count() }} {{ __('rooms') }}</span>
        </div>
        @if($rt->rooms->isNotEmpty())
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>{{ __('Room #') }}</th><th>{{ __('Floor') }}</th><th>{{ __('Status') }}</th><th>{{ __('Notes') }}</th></tr></thead>
                <tbody>
                    @foreach($rt->rooms->sortBy('room_number') as $room)
                    <tr class="tr-hover">
                        <td class="font-mono font-semibold">{{ $room->room_number }}</td>
                        <td>{{ $room->floor ?? '—' }}</td>
                        <td><span class="badge badge-{{ $room->status === 'available' ? 'confirmed' : 'pending' }}">{{ ucfirst($room->status) }}</span></td>
                        <td class="text-xs text-slate-400">{{ $room->notes ?? '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @empty
    <div class="card p-10 text-center text-slate-400">{{ __('No room types configured yet.') }}</div>
    @endforelse
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     TAB: STAFF
══════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tab === 'staff')
<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Position') }}</th><th>{{ __('Status') }}</th><th>{{ __('Since') }}</th></tr></thead>
            <tbody>
                @forelse($hotel->staff as $s)
                <tr class="tr-hover">
                    <td class="font-medium">{{ $s->user->name ?? 'N/A' }}</td>
                    <td class="text-slate-500">{{ $s->user->email ?? '' }}</td>
                    <td><span class="badge badge-confirmed">{{ ucfirst($s->position) }}</span></td>
                    <td>
                        <span class="badge {{ $s->active ? 'badge-confirmed' : 'badge-cancelled' }}">
                            {{ $s->active ? __('Active') : __('Inactive') }}
                        </span>
                    </td>
                    <td class="text-xs text-slate-400">{{ $s->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-10 text-center text-slate-400">{{ __('No staff assigned to this hotel.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     TAB: GUESTS
══════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tab === 'guests')
<form method="GET" class="flex gap-3 mb-5">
    <input type="search" name="search" value="{{ $search ?? '' }}"
           placeholder="{{ __('Search guest name or email…') }}" class="form-input flex-1 text-sm">
    <button type="submit" class="btn-primary btn-sm">{{ __('Search') }}</button>
    @if($search ?? false)
    <a href="{{ route('admin.hotels.hub.guests', $hotel) }}" class="btn-ghost btn-sm">{{ __('Clear') }}</a>
    @endif
</form>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>{{ __('Guest') }}</th><th>{{ __('Email') }}</th><th class="text-center">{{ __('Bookings') }}</th><th class="text-right">{{ __('Total Spent') }}</th><th>{{ __('Last Visit') }}</th></tr></thead>
            <tbody>
                @forelse($guests as $g)
                <tr class="tr-hover">
                    <td class="font-medium">{{ $g->name }}</td>
                    <td class="text-slate-500 text-sm">{{ $g->email }}</td>
                    <td class="text-center font-semibold">{{ $g->hotel_bookings_count }}</td>
                    <td class="text-right font-semibold text-emerald-600 dark:text-emerald-400">
                        {{ money($g->hotel_spend ?? 0) }}
                    </td>
                    <td class="text-xs text-slate-400">{{ $g->updated_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-10 text-center text-slate-400">{{ __('No guests found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($guests) && $guests->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $guests->links() }}</div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════
     TAB: FEATURES (Premium Feature Grants)
══════════════════════════════════════════════════════════════════════════════ --}}
@elseif($tab === 'features')

@php
    $tierColors = [
        'Growth'     => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'border' => 'border-emerald-200 dark:border-emerald-700', 'badge' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400'],
        'Operations' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',   'border' => 'border-blue-200 dark:border-blue-700',   'badge' => 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400'],
        'Revenue'    => ['bg' => 'bg-purple-50 dark:bg-purple-900/20','border' => 'border-purple-200 dark:border-purple-700','badge' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-400'],
        'Premium'    => ['bg' => 'bg-amber-50 dark:bg-amber-900/20',  'border' => 'border-amber-200 dark:border-amber-700',  'badge' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400'],
    ];
@endphp

<div class="space-y-8">
    @foreach($allFeatures as $tier => $features)
    @php $tc = $tierColors[$tier] ?? $tierColors['Growth']; @endphp

    <div>
        {{-- Tier header --}}
        <div class="flex items-center gap-3 mb-4">
            <span class="inline-flex items-center rounded-lg px-3 py-1 text-sm font-bold {{ $tc['badge'] }}">
                {{ $tier }} {{ __('Tier') }}
            </span>
            <div class="flex-1 border-t border-slate-200 dark:border-slate-700"></div>
        </div>

        {{-- Feature cards grid --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($features as $feature)
            @php
                $granted = $grantedByValue[$feature->value] ?? null;
                $active  = $granted && $granted->isActive();
            @endphp

            <div class="relative rounded-2xl border {{ $active ? $tc['border'] . ' ' . $tc['bg'] : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800' }} p-5 transition-all">

                {{-- Active indicator --}}
                @if($active)
                <div class="absolute top-3 right-3 flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ __('Active') }}</span>
                </div>
                @endif

                {{-- Feature info --}}
                <div class="flex items-start gap-3 mb-4">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                        {{ $active ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-slate-700' }}">
                        @if($active)
                            <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-snug">{{ $feature->label() }}</h4>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $feature->description() }}</p>
                    </div>
                </div>

                {{-- Grant info --}}
                @if($active && $granted)
                <div class="mb-3 rounded-xl bg-white/60 dark:bg-slate-900/40 border border-white/80 dark:border-slate-600 px-3 py-2 text-xs space-y-0.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Granted by') }}</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $granted->grantedBy->name ?? 'Admin' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Granted on') }}</span>
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $granted->granted_at->format('d M Y') }}</span>
                    </div>
                    @if($granted->expires_at)
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Expires') }}</span>
                        <span class="font-medium text-amber-600 dark:text-amber-400">{{ $granted->expires_at->format('d M Y') }}</span>
                    </div>
                    @else
                    <div class="flex justify-between">
                        <span class="text-slate-500">{{ __('Expires') }}</span>
                        <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ __('Never (permanent)') }}</span>
                    </div>
                    @endif
                    @if($granted->notes)
                    <div class="pt-1 border-t border-slate-200/60 dark:border-slate-600 text-slate-500 italic">{{ $granted->notes }}</div>
                    @endif
                </div>
                @endif

                {{-- Actions --}}
                @if($active)
                    <form method="POST" action="{{ route('admin.hotels.features.revoke', $hotel) }}"
                          onsubmit="return confirm('Revoke {{ addslashes($feature->label()) }} from {{ addslashes($hotel->name) }}?')">
                        @csrf @method('DELETE')
                        <input type="hidden" name="feature" value="{{ $feature->value }}">
                        <button class="w-full rounded-xl border border-rose-300 dark:border-rose-700 px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">
                            {{ __('Revoke Feature') }}
                        </button>
                    </form>
                @else
                    <details class="group">
                        <summary class="cursor-pointer w-full rounded-xl bg-navy hover:bg-navy/90 dark:bg-amber-500 dark:hover:bg-amber-400 dark:text-slate-900 px-3 py-2 text-xs font-bold text-white transition text-center list-none">
                            {{ __('Grant Feature') }}
                        </summary>
                        <form method="POST" action="{{ route('admin.hotels.features.grant', $hotel) }}"
                              class="mt-3 space-y-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-3">
                            @csrf
                            <input type="hidden" name="feature" value="{{ $feature->value }}">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Expires (leave blank = permanent)') }}</label>
                                <input type="date" name="expires_at"
                                       min="{{ now()->addDay()->toDateString() }}"
                                       class="form-input w-full text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Agreement notes (optional)') }}</label>
                                <input type="text" name="notes" placeholder="{{ __('e.g. 6-month trial, contract #1234') }}"
                                       class="form-input w-full text-xs">
                            </div>
                            <button type="submit"
                                    class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 px-3 py-2 text-xs font-bold text-white transition">
                                {{ __('Confirm Grant') }}
                            </button>
                        </form>
                    </details>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

@endif {{-- end tab switch --}}

@endsection
