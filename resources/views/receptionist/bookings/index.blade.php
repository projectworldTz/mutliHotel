@extends('layouts.receptionist')
@section('title', 'Bookings')
@section('page-title', 'Bookings')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    {{-- Status tabs --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'checked_in' => 'In-House', 'checked_out' => 'Checked Out', 'cancelled' => 'Cancelled'] as $val => $label)
        <a href="{{ route('receptionist.bookings.index', ['status' => $val, 'search' => $search]) }}"
           class="{{ $status === $val ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="flex gap-2">
        <form method="GET" action="{{ route('receptionist.bookings.index') }}" class="flex gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name / booking #"
                   class="form-input w-52">
            <button type="submit" class="btn-outline btn-sm">Search</button>
        </form>
        <a href="{{ route('receptionist.bookings.create') }}" class="btn-gold btn-sm">+ New Booking</a>
    </div>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Booking #</th>
                <th>Guest</th>
                <th>Room Type</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Status</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
            <tr class="tr-hover">
                <td class="font-mono text-xs font-semibold">{{ $booking->booking_number }}</td>
                <td>
                    <p class="font-medium text-slate-900 dark:text-white">{{ $booking->user->name }}</p>
                    <p class="text-xs text-slate-500">{{ $booking->user->email }}</p>
                </td>
                <td>{{ $booking->roomType->name ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}</td>
                <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></td>
                <td class="font-semibold">TZS {{ number_format($booking->grand_total, 0) }}</td>
                <td>
                    <a href="{{ route('receptionist.bookings.show', $booking) }}" class="btn-ghost btn-sm">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="py-10 text-center text-slate-500">No bookings found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
