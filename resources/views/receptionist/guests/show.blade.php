@extends('layouts.receptionist')
@section('title', $guest->name)
@section('page-title', $guest->name)

@section('content')
<div class="mb-4"><a href="{{ route('receptionist.guests.index') }}" class="btn-ghost btn-sm">← Guests</a></div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2">
        <div class="card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700">
                <h2 class="font-bold text-slate-900 dark:text-white">Stay History at {{ $hotel->name }}</h2>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>Booking #</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Total</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr class="tr-hover">
                            <td class="font-mono text-xs font-semibold">{{ $booking->booking_number }}</td>
                            <td>{{ $booking->roomType->name ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}</td>
                            <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></td>
                            <td>TZS {{ number_format($booking->grand_total, 0) }}</td>
                            <td><a href="{{ route('receptionist.bookings.show', $booking) }}" class="btn-ghost btn-sm">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-500">No bookings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-5 text-sm space-y-3">
            <h3 class="font-bold text-slate-900 dark:text-white">Guest Profile</h3>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-navy/10 dark:bg-navy/30 text-2xl font-bold text-navy dark:text-navy-light">
                {{ strtoupper(substr($guest->name, 0, 1)) }}
            </div>
            <div><span class="text-slate-500">Name</span><p class="font-semibold">{{ $guest->name }}</p></div>
            <div><span class="text-slate-500">Email</span><p class="font-semibold">{{ $guest->email }}</p></div>
            <div><span class="text-slate-500">Phone</span><p class="font-semibold">{{ $guest->phone ?? '—' }}</p></div>
            <div><span class="text-slate-500">Member Since</span><p class="font-semibold">{{ $guest->created_at->format('d M Y') }}</p></div>
            <div><span class="text-slate-500">Total Stays</span><p class="font-semibold">{{ $bookings->count() }}</p></div>
        </div>

        <a href="{{ route('receptionist.bookings.create') }}" class="btn-gold w-full block text-center">+ New Booking for Guest</a>
    </div>
</div>
@endsection
