@extends('layouts.admin')
@section('title', 'Bookings')
@section('page-title', 'All Bookings')

@section('content')
<div class="mb-5 flex flex-wrap items-center gap-3">
    <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               class="form-input w-48 py-2 text-sm" placeholder="Booking # or guest…">
        <select name="status" class="form-select py-2 text-sm w-auto">
            <option value="">All Status</option>
            @foreach(['pending','confirmed','checked_in','checked_out','cancelled','refunded'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="form-input py-2 text-sm w-auto" placeholder="From">
        <input type="date" name="to" value="{{ request('to') }}" class="form-input py-2 text-sm w-auto" placeholder="To">
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.bookings.index') }}" class="btn-ghost btn-sm">Reset</a>
    </form>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Booking #</th>
                <th>Guest</th>
                <th>Hotel</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Total</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $b)
            <tr class="tr-hover">
                <td class="font-mono text-xs">{{ $b->booking_number }}</td>
                <td>{{ $b->user->name ?? 'N/A' }}</td>
                <td>{{ $b->hotel->name ?? 'N/A' }}</td>
                <td class="text-sm whitespace-nowrap">{{ \Carbon\Carbon::parse($b->check_in)->format('d M Y') }}</td>
                <td class="text-sm whitespace-nowrap">{{ \Carbon\Carbon::parse($b->check_out)->format('d M Y') }}</td>
                <td class="font-semibold">${{ number_format($b->total_amount ?? 0, 2) }}</td>
                <td><span class="badge badge-{{ $b->status }}">{{ ucfirst(str_replace('_',' ',$b->status)) }}</span></td>
                <td><a href="{{ route('admin.bookings.show', $b) }}" class="btn-ghost btn-sm">View</a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-10 text-slate-500">No bookings found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $bookings->withQueryString()->links() }}</div>
@endsection
