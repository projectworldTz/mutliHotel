@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="page-header">
        <h1 class="page-title">Checkout</h1>
    </div>

    <form method="POST" action="{{ route('booking.store') }}" x-data="{ payment: 'stripe' }">
        @csrf
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-5">
                {{-- Guest details --}}
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Guest Details</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Adults</label>
                            <input type="number" name="guests_adults" value="{{ old('guests_adults', 1) }}"
                                   min="1" max="20" class="form-input @error('guests_adults') border-rose-500 @enderror">
                            @error('guests_adults') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Children</label>
                            <input type="number" name="guests_children" value="{{ old('guests_children', 0) }}"
                                   min="0" max="10" class="form-input">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="form-label">Special Requests <span class="font-normal text-slate-400">(optional)</span></label>
                        <textarea name="special_requests" rows="3" class="form-textarea"
                                  placeholder="e.g. early check-in, ground floor, dietary requirements…">{{ old('special_requests') }}</textarea>
                    </div>
                </div>

                {{-- Payment method --}}
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Payment Method</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach([
                            ['stripe',  'Credit / Debit Card', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                            ['paypal',  'PayPal',               'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                            ['bank',    'Bank Transfer',        'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
                            ['cash',    'Pay at Hotel',         'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                        ] as [$val, $label, $icon])
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-4 transition"
                               :class="payment === '{{ $val }}'
                                   ? 'border-navy bg-navy/5 dark:border-navy-light dark:bg-navy/10'
                                   : 'border-slate-200 dark:border-slate-700 hover:border-slate-300'">
                            <input type="radio" name="payment_method" value="{{ $val }}"
                                   x-model="payment" class="sr-only">
                            <svg class="h-5 w-5 shrink-0" :class="payment === '{{ $val }}' ? 'text-navy dark:text-navy-light' : 'text-slate-400'"
                                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                            </svg>
                            <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $label }}</span>
                            <div class="ml-auto" :class="payment === '{{ $val }}' ? '' : 'invisible'">
                                <svg class="h-4 w-4 text-navy dark:text-navy-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Terms --}}
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" name="agree_terms" value="1"
                           class="mt-0.5 rounded border-slate-300 text-navy @error('agree_terms') border-rose-500 @enderror">
                    <span class="text-sm text-slate-600 dark:text-slate-300">
                        I agree to the <a href="#" class="text-navy underline">Terms of Service</a> and
                        <a href="#" class="text-navy underline">Cancellation Policy</a>.
                    </span>
                </label>
                @error('agree_terms') <p class="form-error -mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Order summary --}}
            <div>
                <div class="card p-5 sticky top-20">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4">Summary</h3>
                    @foreach($cart->items as $item)
                    <div class="mb-3 pb-3 border-b border-slate-100 dark:border-slate-700 last:border-0 last:mb-0 last:pb-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->roomType->name ?? 'Room' }}</p>
                        <p class="text-xs text-slate-500">{{ $item->roomType->hotel->name ?? '' }}</p>
                        <div class="mt-1 flex justify-between text-xs text-slate-500">
                            <span>{{ \Carbon\Carbon::parse($item->check_in)->format('d M') }} – {{ \Carbon\Carbon::parse($item->check_out)->format('d M Y') }}</span>
                            <span class="font-semibold">${{ number_format($item->sub_total ?? 0, 2) }}</span>
                        </div>
                    </div>
                    @endforeach

                    <div class="mt-3 border-t border-slate-100 dark:border-slate-700 pt-3 space-y-1 text-sm">
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
                        <div class="flex justify-between font-bold text-base text-slate-900 dark:text-white pt-1">
                            <span>Total</span>
                            <span>${{ number_format(($cart->sub_total ?? 0) - ($cart->discount ?? 0), 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-gold w-full mt-5">
                        Confirm &amp; Book
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
