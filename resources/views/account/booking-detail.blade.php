@extends('layouts.app')
@section('title', __('Booking') . ' ' . $booking->booking_number)

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-4 flex items-center gap-2">
        <a href="{{ route('account.bookings') }}" class="btn-ghost btn-sm">{{ __('← My Bookings') }}</a>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-5">

            {{-- Booking summary --}}
            <div class="card p-6">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Booking #') }}{{ $booking->booking_number }}</h1>
                        <p class="text-sm text-slate-500 mt-0.5">{{ __('Made on') }} {{ $booking->created_at->format('d M Y') }}</p>
                    </div>
                    <span class="badge badge-{{ $booking->status }} text-sm px-3 py-1">
                        {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Check-in') }}</p>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $booking->check_in->format('D, d M Y') }}</p>
                        <p class="text-xs text-slate-500">{{ __('from') }} {{ $booking->hotel->check_in_time ?? '14:00' }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">{{ __('Check-out') }}</p>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $booking->check_out->format('D, d M Y') }}</p>
                        <p class="text-xs text-slate-500">{{ __('by') }} {{ $booking->hotel->check_out_time ?? '11:00' }}</p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3 text-sm">
                    <div><span class="text-slate-500">{{ __('Nights') }}</span><p class="font-semibold mt-0.5">{{ $booking->nights }}</p></div>
                    <div><span class="text-slate-500">{{ __('Adults') }}</span><p class="font-semibold mt-0.5">{{ $booking->guests_adults }}</p></div>
                    <div><span class="text-slate-500">{{ __('Children') }}</span><p class="font-semibold mt-0.5">{{ $booking->guests_children }}</p></div>
                </div>

                @if($booking->special_requests)
                <div class="mt-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3 text-sm">
                    <p class="font-semibold text-amber-700 dark:text-amber-400 mb-1">{{ __('Special Requests') }}</p>
                    <p class="text-amber-800 dark:text-amber-300">{{ $booking->special_requests }}</p>
                </div>
                @endif
            </div>

            {{-- Hotel info --}}
            <div class="card p-6">
                <h3 class="font-bold text-slate-900 dark:text-white mb-3">{{ __('Hotel') }}</h3>
                <div class="flex items-center gap-4">
                    @if($booking->hotel->featuredImage)
                    <img src="{{ asset('storage/' . $booking->hotel->featuredImage->path) }}"
                         alt="{{ $booking->hotel->name }}"
                         class="h-16 w-16 rounded-xl object-cover flex-shrink-0">
                    @endif
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $booking->hotel->name }}</p>
                        <p class="text-sm text-slate-500">{{ $booking->hotel->address ?? $booking->hotel->city }}</p>
                        <a href="{{ route('hotels.show', $booking->hotel) }}" class="text-sm text-navy dark:text-navy-light underline">{{ __('View hotel') }} →</a>
                    </div>
                </div>
            </div>

            {{-- Booked rooms --}}
            @if($booking->rooms->isNotEmpty())
            <div class="card">
                <div class="p-5 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Booked Rooms') }}</h3>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    @foreach($booking->rooms as $room)
                    <div class="p-5 flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold text-slate-900 dark:text-white">{{ $room->roomType->name ?? __('Room') }}</p>
                            <p class="text-sm text-slate-500 mt-0.5">
                                {{ $room->check_in->format('d M') }} → {{ $room->check_out->format('d M Y') }}
                                · {{ $room->nights }} {{ $room->nights !== 1 ? __('nights') : __('night') }}
                            </p>
                        </div>
                        <p class="font-semibold text-slate-900 dark:text-white whitespace-nowrap">
                            {{ money($room->sub_total) }}
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            {{-- Price summary --}}
            <div class="card p-5 text-sm space-y-2">
                <h3 class="font-bold text-slate-900 dark:text-white mb-3">{{ __('Price Summary') }}</h3>
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Subtotal') }}</span><span>{{ money($booking->sub_total) }}</span></div>
                @if($booking->discount_total > 0)
                <div class="flex justify-between text-emerald-600"><span>{{ __('Discount') }}</span><span>−{{ money($booking->discount_total) }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Tax') }} ({{ $booking->tax_rate }}%)</span><span>{{ money($booking->tax_total) }}</span></div>
                <div class="flex justify-between font-bold text-base border-t border-slate-200 dark:border-slate-700 pt-2 mt-1">
                    <span>{{ __('Total') }}</span><span>{{ money($booking->grand_total) }}</span>
                </div>
                @if($booking->payment)
                <div class="flex justify-between text-slate-500 pt-1">
                    <span>{{ __('Payment') }}</span>
                    <span class="capitalize">{{ str_replace('_', ' ', $booking->payment->method) }}</span>
                </div>
                @endif
            </div>

            {{-- Actions --}}
            @if($booking->is_cancellable)
            <div class="card p-5">
                <h3 class="font-bold text-slate-900 dark:text-white mb-3">{{ __('Actions') }}</h3>
                <form method="POST" action="{{ route('booking.cancel', $booking) }}"
                      x-data x-on:submit.prevent="if(confirm('{{ __('Are you sure you want to cancel this booking?') }}')) $el.submit()">
                    @csrf
                    <button class="btn-danger w-full">{{ __('Cancel Booking') }}</button>
                </form>
            </div>
            @endif

            {{-- Invoice --}}
            @if($booking->invoice)
            <a href="{{ route('booking.invoice', $booking) }}"
               class="btn-outline w-full block text-center">{{ __('Download Invoice') }}</a>
            @endif
        </div>
    </div>
</div>
@endsection
