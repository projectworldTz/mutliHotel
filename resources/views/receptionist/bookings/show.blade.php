@extends('layouts.receptionist')
@section('title', 'Booking ' . $booking->booking_number)
@section('page-title', 'Booking ' . $booking->booking_number)

@section('content')
<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('receptionist.bookings.index') }}" class="btn-ghost btn-sm">← Bookings</a>
    <a href="{{ route('receptionist.bookings.invoice', $booking) }}" class="btn-outline btn-sm ml-auto" target="_blank">
        🖨 Print Invoice
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-5">

        {{-- Booking summary --}}
        <div class="card p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $booking->booking_number }}</h2>
                    <p class="text-sm text-slate-500">Created {{ $booking->created_at->format('d M Y, H:i') }}</p>
                </div>
                <span class="badge badge-{{ $booking->status }} text-sm px-3 py-1">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Check-in</p>
                    <p class="font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->check_in)->format('D, d M Y') }}</p>
                    <p class="text-xs text-slate-500">from {{ $hotel->check_in_time ?? '14:00' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">Check-out</p>
                    <p class="font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->check_out)->format('D, d M Y') }}</p>
                    <p class="text-xs text-slate-500">by {{ $hotel->check_out_time ?? '11:00' }}</p>
                </div>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3 text-sm">
                <div><span class="text-slate-500">Room Type</span><p class="font-semibold mt-0.5">{{ $booking->roomType->name ?? '—' }}</p></div>
                <div><span class="text-slate-500">Guests</span><p class="font-semibold mt-0.5">{{ $booking->guests }}</p></div>
                <div><span class="text-slate-500">Nights</span><p class="font-semibold mt-0.5">{{ $booking->nights }}</p></div>
            </div>
        </div>

        {{-- Guest info --}}
        <div class="card p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">Guest Information</h3>
            <div class="grid gap-3 sm:grid-cols-2 text-sm">
                <div><span class="text-slate-500">Name</span><p class="font-semibold mt-0.5">{{ $booking->user->name }}</p></div>
                <div><span class="text-slate-500">Email</span><p class="font-semibold mt-0.5">{{ $booking->user->email }}</p></div>
                <div><span class="text-slate-500">Phone</span><p class="font-semibold mt-0.5">{{ $booking->user->phone ?? '—' }}</p></div>
                <div><span class="text-slate-500">Payment</span><p class="font-semibold mt-0.5 capitalize">{{ str_replace('_', ' ', $booking->payment_method) }}</p></div>
            </div>
            @if($booking->special_requests)
            <div class="mt-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3">
                <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">Special Requests</p>
                <p class="text-sm text-amber-800 dark:text-amber-300">{{ $booking->special_requests }}</p>
            </div>
            @endif
        </div>

        {{-- Price breakdown --}}
        <div class="card p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">Price Breakdown</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Room ({{ $booking->nights }} nights × TZS {{ number_format($booking->base_price, 0) }})</span><span>TZS {{ number_format($booking->subtotal, 0) }}</span></div>
                @if($booking->discount_amount > 0)
                <div class="flex justify-between text-emerald-600"><span>Discount</span><span>−TZS {{ number_format($booking->discount_amount, 0) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-slate-500">Tax ({{ $booking->tax_rate ?? 18 }}%)</span><span>TZS {{ number_format($booking->tax_amount, 0) }}</span></div>
                <div class="flex justify-between font-bold text-base border-t border-slate-200 dark:border-slate-700 pt-2 mt-1">
                    <span>Total</span><span>TZS {{ number_format($booking->grand_total, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions sidebar --}}
    <div class="space-y-4">
        <div class="card p-5 space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Actions</h3>

            @if($booking->status === 'pending')
            <form method="POST" action="{{ route('receptionist.bookings.confirm', $booking) }}">
                @csrf
                <button class="btn-success w-full">Confirm Booking</button>
            </form>
            @endif

            @if($booking->status === 'confirmed')
            <form method="POST" action="{{ route('receptionist.bookings.check-in', $booking) }}">
                @csrf
                <button class="btn-primary w-full">Check In Guest</button>
            </form>
            @endif

            @if($booking->status === 'checked_in')
            <form method="POST" action="{{ route('receptionist.bookings.check-out', $booking) }}">
                @csrf
                <button class="btn-outline w-full">Check Out Guest</button>
            </form>
            @endif

            @if(!in_array($booking->status, ['cancelled','refunded','checked_out']))
            <form method="POST" action="{{ route('receptionist.bookings.cancel', $booking) }}"
                  x-data x-on:submit.prevent="if(confirm('Cancel this booking?')) $el.submit()">
                @csrf
                <input type="hidden" name="reason" value="Cancelled at reception.">
                <button class="btn-danger w-full">Cancel Booking</button>
            </form>
            @endif

            <a href="{{ route('receptionist.bookings.invoice', $booking) }}" target="_blank"
               class="btn-ghost w-full block text-center">Print Invoice / Receipt</a>
        </div>

        <div class="card p-5 text-sm space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">Quick Info</h3>
            <div class="flex justify-between"><span class="text-slate-500">Hotel</span><span>{{ $hotel->name }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Room Type</span><span>{{ $booking->roomType->name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Booked</span><span>{{ $booking->created_at->diffForHumans() }}</span></div>
        </div>
    </div>
</div>
@endsection
