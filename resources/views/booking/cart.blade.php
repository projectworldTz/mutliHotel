@extends('layouts.app')
@section('title', 'Reservation Cart')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="page-header">
        <h1 class="page-title">Reservation Cart</h1>
    </div>

    @if(!$cart || $cart->items->isEmpty())
        <div class="card flex flex-col items-center justify-center py-20 text-center">
            <svg class="h-16 w-16 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Your cart is empty</h3>
            <p class="mt-1 text-sm text-slate-500">Browse hotels and reserve a room to get started.</p>
            <a href="{{ route('hotels.index') }}" class="btn-primary mt-5">Browse Hotels</a>
        </div>
    @else
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Cart items --}}
        <div class="lg:col-span-2 space-y-4">
            @foreach($cart->items as $item)
            <div class="card p-5">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-slate-900 dark:text-white">{{ $item->roomType->name ?? 'Room' }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ $item->roomType->hotel->name ?? '' }}</p>
                        <div class="mt-2 flex flex-wrap gap-3 text-sm text-slate-600 dark:text-slate-300">
                            <span>
                                <span class="font-medium">Check-in:</span>
                                {{ \Carbon\Carbon::parse($item->check_in)->format('D, d M Y') }}
                            </span>
                            <span>
                                <span class="font-medium">Check-out:</span>
                                {{ \Carbon\Carbon::parse($item->check_out)->format('D, d M Y') }}
                            </span>
                            <span>
                                <span class="font-medium">Guests:</span> {{ $item->guests }}
                            </span>
                            <span>
                                <span class="font-medium">Nights:</span>
                                {{ \Carbon\Carbon::parse($item->check_in)->diffInDays($item->check_out) }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="text-xl font-bold text-navy dark:text-navy-light">
                            ${{ number_format($item->sub_total ?? 0, 2) }}
                        </p>
                        <p class="text-xs text-slate-500">${{ number_format($item->roomType->base_price ?? 0, 2) }}/night</p>
                        <form method="POST" action="{{ route('booking.cart.destroy', $item) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 transition">Remove</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Order summary --}}
        <div class="space-y-4">
            {{-- Coupon --}}
            <div class="card p-5" x-data="{ applying: false }">
                <h3 class="font-bold text-slate-900 dark:text-white mb-3">Coupon Code</h3>
                <form method="POST" action="{{ route('booking.cart.coupon') }}">
                    @csrf
                    <div class="flex gap-2">
                        <input type="text" name="coupon_code"
                               value="{{ $cart->coupon_code ?? '' }}"
                               class="form-input flex-1" placeholder="Enter code">
                        <button type="submit" class="btn-outline btn-sm">Apply</button>
                    </div>
                </form>
                @if($cart->coupon_code)
                    <p class="mt-2 text-sm text-emerald-600 dark:text-emerald-400">
                        Coupon "{{ $cart->coupon_code }}" applied!
                    </p>
                @endif
            </div>

            {{-- Totals --}}
            <div class="card p-5">
                <h3 class="font-bold text-slate-900 dark:text-white mb-4">Order Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                        <span>Subtotal</span>
                        <span>${{ number_format($cart->sub_total ?? 0, 2) }}</span>
                    </div>
                    @if(($cart->discount ?? 0) > 0)
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                        <span>Discount</span>
                        <span>-${{ number_format($cart->discount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-slate-900 dark:text-white text-base pt-2 border-t border-slate-100 dark:border-slate-700">
                        <span>Total</span>
                        <span>${{ number_format(($cart->sub_total ?? 0) - ($cart->discount ?? 0), 2) }}</span>
                    </div>
                </div>
                <a href="{{ route('booking.checkout') }}" class="btn-gold w-full mt-5 text-center block">
                    Proceed to Checkout
                </a>
                <a href="{{ route('hotels.index') }}" class="btn-ghost btn-sm w-full text-center block mt-2">
                    Add More Hotels
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
