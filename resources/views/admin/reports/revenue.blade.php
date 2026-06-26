@extends('layouts.admin')
@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')

@section('content')
<div class="mb-5 flex gap-3">
    <a href="{{ route('admin.reports.revenue') }}" class="btn-primary btn-sm">Revenue</a>
    <a href="{{ route('admin.reports.occupancy') }}" class="btn-outline btn-sm">Occupancy</a>
</div>

<div class="card p-6">
    <h2 class="font-bold text-slate-900 dark:text-white mb-4">Revenue by Month</h2>
    <div class="h-72">
        <canvas id="revenueChart"></canvas>
    </div>
</div>

<div class="mt-6 card table-wrap">
    <table class="table">
        <thead>
            <tr><th>Month</th><th>Bookings</th><th>Revenue</th></tr>
        </thead>
        <tbody>
            @foreach($report as $row)
            <tr class="tr-hover">
                <td>{{ $row['month'] }}</td>
                <td>{{ $row['bookings'] ?? 0 }}</td>
                <td class="font-semibold">${{ number_format($row['total'] ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const data = @json($report);
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(r => r.month),  // already "Jun 2026" from controller
            datasets: [{
                label: 'Revenue ($)',
                data: data.map(r => r.total),
                borderColor: '#C9A227',
                backgroundColor: 'rgba(201,162,39,0.15)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } } }
        }
    });
})();
</script>
@endpush
