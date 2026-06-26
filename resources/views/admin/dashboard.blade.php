@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- Stats row --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    @foreach([
        ['Hotels',            $hotelStats['total']      ?? 0, 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['Active Bookings',   $bookingStats['active']   ?? 0, 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['Revenue (month)',   '$' . number_format($bookingStats['revenue_month'] ?? 0, 0), 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Pending Approval',  $hotelStats['pending']    ?? 0, 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
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

<div class="grid gap-6 lg:grid-cols-3">
    {{-- Revenue chart --}}
    <div class="lg:col-span-2 card p-5">
        <h3 class="font-bold text-slate-900 dark:text-white mb-4">Revenue — Last 12 Months</h3>
        <div class="h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Pending hotels --}}
    <div class="card">
        <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
            <h3 class="font-bold text-slate-900 dark:text-white">Pending Hotels</h3>
            <a href="{{ route('admin.hotels.index', ['status' => 'pending']) }}" class="btn-ghost btn-sm">View All</a>
        </div>
        @if($pendingHotels->isEmpty())
            <p class="p-5 text-sm text-slate-500">No hotels pending approval.</p>
        @else
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($pendingHotels as $h)
            <div class="flex items-center justify-between px-5 py-3">
                <div>
                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $h->name }}</p>
                    <p class="text-xs text-slate-500">{{ $h->city }} · {{ $h->owner->name ?? 'Unknown' }}</p>
                </div>
                <a href="{{ route('admin.hotels.show', $h) }}" class="btn-primary btn-sm">Review</a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Recent bookings --}}
<div class="mt-6 card">
    <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
        <h3 class="font-bold text-slate-900 dark:text-white">Recent Bookings</h3>
        <a href="{{ route('admin.bookings.index') }}" class="btn-ghost btn-sm">View All</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Booking #</th>
                    <th>Guest</th>
                    <th>Hotel</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBookings as $b)
                <tr class="tr-hover">
                    <td class="font-mono text-xs">{{ $b->booking_number }}</td>
                    <td>{{ $b->user->name ?? 'N/A' }}</td>
                    <td>{{ $b->hotel->name ?? 'N/A' }}</td>
                    <td class="font-semibold">${{ number_format($b->grand_total ?? 0, 2) }}</td>
                    <td><span class="badge badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td>
                    <td><a href="{{ route('admin.bookings.show', $b) }}" class="btn-ghost btn-sm">View</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const revenue = @json($revenue);
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: revenue.map(r => {
                const d = new Date(r.year, r.month - 1);
                return d.toLocaleString('default', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'Revenue ($)',
                data: revenue.map(r => r.total),
                backgroundColor: 'rgba(27, 58, 107, 0.7)',
                borderColor: '#1B3A6B',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } }
            }
        }
    });
})();
</script>
@endpush
