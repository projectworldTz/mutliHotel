@extends('layouts.owner')
@section('title', 'Coupons — ' . $hotel->name)
@section('page-title', $hotel->name . ' — Coupons')

@section('content')
<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">← {{ $hotel->name }}</a>
    <a href="{{ route('owner.hotels.coupons.create', $hotel) }}" class="btn-primary btn-sm ml-auto">+ New Coupon</a>
</div>

@if(session('success'))
<div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 text-sm text-emerald-700 dark:text-emerald-300">
    {{ session('success') }}
</div>
@endif

{{-- Info banner --}}
<div class="mb-5 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4 flex gap-3">
    <svg class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
    </svg>
    <div class="text-sm text-blue-700 dark:text-blue-300">
        <p class="font-semibold">How to share coupon codes with guests</p>
        <p class="mt-0.5">Copy the code from this list and share it via WhatsApp, SMS, your social media pages, or print it on a card to give guests at check-out. Guests enter the code at checkout to get their discount.</p>
    </div>
</div>

<div class="card">
    @if($coupons->isEmpty())
        <div class="p-8 text-center text-slate-400 dark:text-slate-500">
            <svg class="mx-auto h-10 w-10 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/>
            </svg>
            <p class="font-medium">No coupons yet</p>
            <p class="text-xs mt-1">Create a coupon and share the code to attract more bookings.</p>
            <a href="{{ route('owner.hotels.coupons.create', $hotel) }}" class="btn-primary btn-sm mt-4 inline-block">
                Create First Coupon
            </a>
        </div>
    @else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Room Type</th>
                    <th>Uses</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($coupons as $coupon)
                <tr class="tr-hover">
                    <td>
                        <div class="flex items-center gap-2"
                             x-data="{ copied: false }">
                            <span class="font-mono font-bold text-slate-900 dark:text-white tracking-wide">
                                {{ $coupon->code }}
                            </span>
                            <button type="button"
                                    @click="navigator.clipboard.writeText('{{ $coupon->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="text-slate-400 hover:text-navy dark:hover:text-navy-light transition"
                                    title="Copy code">
                                <svg x-show="!copied" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/>
                                </svg>
                                <svg x-show="copied" x-cloak class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                    <td>
                        @if($coupon->type === 'percentage')
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $coupon->value }}% off</span>
                        @else
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">TZS {{ number_format($coupon->value, 0) }} off</span>
                        @endif
                        @if($coupon->min_booking_amount)
                            <p class="text-xs text-slate-400">min TZS {{ number_format($coupon->min_booking_amount, 0) }}</p>
                        @endif
                    </td>
                    <td class="text-sm">
                        {{ $coupon->roomType->name ?? 'All rooms' }}
                    </td>
                    <td class="text-sm">
                        {{ $coupon->uses }}
                        @if($coupon->max_uses)
                            / {{ $coupon->max_uses }}
                        @else
                            / ∞
                        @endif
                    </td>
                    <td class="text-sm">
                        @if($coupon->expires_at)
                            <span class="{{ $coupon->expires_at->isPast() ? 'text-rose-500' : 'text-slate-600 dark:text-slate-300' }}">
                                {{ $coupon->expires_at->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-slate-400">Never</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('owner.hotels.coupons.toggle', [$hotel, $coupon]) }}">
                            @csrf
                            <button type="submit"
                                    class="text-xs font-semibold px-2 py-1 rounded-lg transition
                                           {{ $coupon->active
                                               ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400'
                                               : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ $coupon->active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('owner.hotels.coupons.destroy', [$hotel, $coupon]) }}"
                              x-data
                              @submit.prevent="if(confirm('Delete coupon {{ $coupon->code }}?')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-500 hover:text-rose-700 font-medium">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
        <div class="p-4">{{ $coupons->links() }}</div>
    @endif
    @endif
</div>
@endsection
