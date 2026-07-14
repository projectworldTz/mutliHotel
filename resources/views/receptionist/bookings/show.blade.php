@extends('layouts.receptionist')
@section('title', 'Booking ' . $booking->booking_number)
@section('page-title', 'Booking ' . $booking->booking_number)

@section('content')
<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('receptionist.bookings.index') }}" class="btn-ghost btn-sm">{{ __('← Bookings') }}</a>
    <a href="{{ route('receptionist.bookings.invoice', $booking) }}" class="btn-outline btn-sm ml-auto" target="_blank">
        🖨 {{ __('Print Invoice') }}
    </a>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-5">

        {{-- Booking summary --}}
        <div class="card p-6">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $booking->booking_number }}</h2>
                    <p class="text-sm text-slate-500">{{ __('Created') }} {{ $booking->created_at->format('d M Y, H:i') }}</p>
                </div>
                <span class="badge badge-{{ $booking->status }} text-sm px-3 py-1">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Check-in') }}</p>
                    <p class="font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->check_in)->format('D, d M Y') }}</p>
                    <p class="text-xs text-slate-500">{{ __('from') }} {{ $hotel->check_in_time ?? '14:00' }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Check-out') }}</p>
                    <p class="font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->check_out)->format('D, d M Y') }}</p>
                    <p class="text-xs text-slate-500">{{ __('by') }} {{ $hotel->check_out_time ?? '11:00' }}</p>
                </div>
            </div>

            <div class="mt-4 grid gap-3 sm:grid-cols-3 text-sm">
                <div><span class="text-slate-500">{{ __('Room Type') }}</span><p class="font-semibold mt-0.5">{{ $booking->roomType->name ?? '—' }}</p></div>
                <div><span class="text-slate-500">{{ __('Guests') }}</span><p class="font-semibold mt-0.5">{{ $booking->guests }}</p></div>
                <div><span class="text-slate-500">{{ __('Nights') }}</span><p class="font-semibold mt-0.5">{{ $booking->nights }}</p></div>
            </div>
        </div>

        {{-- Guest info --}}
        <div class="card p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">{{ __('Guest Information') }}</h3>
            <div class="grid gap-3 sm:grid-cols-2 text-sm">
                <div><span class="text-slate-500">{{ __('Name') }}</span><p class="font-semibold mt-0.5">{{ $booking->user->name }}</p></div>
                <div><span class="text-slate-500">{{ __('Email') }}</span><p class="font-semibold mt-0.5">{{ $booking->user->email }}</p></div>
                <div><span class="text-slate-500">{{ __('Phone') }}</span><p class="font-semibold mt-0.5">{{ $booking->user->phone ?? '—' }}</p></div>
                <div><span class="text-slate-500">{{ __('Payment') }}</span><p class="font-semibold mt-0.5 capitalize">{{ str_replace('_', ' ', $booking->payment_method) }}</p></div>
            </div>
            @if($booking->special_requests)
            <div class="mt-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3">
                <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 mb-1">{{ __('Special Requests') }}</p>
                <p class="text-sm text-amber-800 dark:text-amber-300">{{ $booking->special_requests }}</p>
            </div>
            @endif
        </div>

        {{-- Price breakdown --}}
        <div class="card p-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">{{ __('Price Breakdown') }}</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Room') }} ({{ $booking->nights }} {{ __('nights') }} × {{ money($booking->base_price) }})</span><span>{{ money($booking->subtotal) }}</span></div>
                @foreach($booking->mealPackages as $mp)
                <div class="flex justify-between"><span class="text-slate-500">{{ $mp->name }} @if($mp->quantity > 1) × {{ $mp->quantity }} @endif</span><span>{{ money($mp->sub_total) }}</span></div>
                @endforeach
                @if($booking->discount_amount > 0)
                <div class="flex justify-between text-emerald-600"><span>{{ __('Discount') }}</span><span>−{{ money($booking->discount_amount) }}</span></div>
                @endif
                @if($booking->tax_amount > 0)
                <div class="flex justify-between"><span class="text-slate-500">{{ __('Tax') }} ({{ $booking->tax_rate }}%)</span><span>{{ money($booking->tax_amount) }}</span></div>
                @endif
                <div class="flex justify-between font-bold text-base border-t border-slate-200 dark:border-slate-700 pt-2 mt-1">
                    <span>{{ __('Total') }}</span><span>{{ money($booking->grand_total) }}</span>
                </div>

                @if($booking->invoice && $booking->invoice->isCancelled() && $booking->invoice->cancellation_deduction)
                <div class="mt-3 pt-3 border-t border-dashed border-rose-200 dark:border-rose-800 space-y-1.5">
                    <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wide mb-1">{{ __('Cancellation Adjustment') }}</p>
                    <div class="flex justify-between text-rose-600 dark:text-rose-400">
                        <span>{{ __('Deduction') }} ({{ number_format((float) $booking->invoice->deduction_percentage, 0) }}%)</span>
                        <span>−{{ money($booking->invoice->cancellation_deduction) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-emerald-600 dark:text-emerald-400">
                        <span>{{ __('Refund Due') }} ({{ number_format(100 - (float) $booking->invoice->deduction_percentage, 0) }}%)</span>
                        <span>{{ money($booking->invoice->refund_amount) }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Actions sidebar --}}
    <div class="space-y-4">
        <div class="card p-5 space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">{{ __('Actions') }}</h3>

            @if($booking->status === 'pending')
            <form method="POST" action="{{ route('receptionist.bookings.confirm', $booking) }}">
                @csrf
                <button class="btn-success w-full">{{ __('Confirm Booking') }}</button>
            </form>
            @endif

            @if($booking->status === 'confirmed')
            <form method="POST" action="{{ route('receptionist.bookings.check-in', $booking) }}">
                @csrf
                <button class="btn-primary w-full">{{ __('Check In Guest') }}</button>
            </form>
            @endif

            @if($booking->status === 'checked_in')
            <form method="POST" action="{{ route('receptionist.bookings.check-out', $booking) }}">
                @csrf
                <button class="btn-outline w-full">{{ __('Check Out Guest') }}</button>
            </form>
            @endif

            @if($booking->is_cancellable)
            <form method="POST" action="{{ route('receptionist.bookings.cancel', $booking) }}"
                  x-data x-on:submit.prevent="if(confirm('{{ __('Cancel this booking?') }}')) $el.submit()">
                @csrf
                <input type="hidden" name="reason" value="Cancelled at reception.">
                <button class="btn-danger w-full">{{ __('Cancel Booking') }}</button>
            </form>
            @endif

            {{-- Emergency Cancellation (60% deduction / 40% refund) --}}
            @if(in_array($booking->status, ['confirmed', 'checked_in']))
            @php $cancellationApproval = $booking->cancellationApproval; @endphp

            @if(! $cancellationApproval)
            {{-- No request yet — show the request form --}}
            <div x-data="{ open: false }" class="mt-1">
                <button @click="open = !open"
                        class="w-full flex items-center justify-center gap-2 rounded-xl border-2 border-dashed border-rose-300 dark:border-rose-700 px-4 py-2.5 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/10 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Request Emergency Cancellation
                </button>

                <div x-show="open" x-cloak class="mt-3 rounded-xl bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800 p-4">
                    <p class="text-xs font-semibold text-rose-700 dark:text-rose-300 mb-2 uppercase tracking-wide">⚠ Emergency Cancellation</p>
                    <p class="text-xs text-rose-600 dark:text-rose-400 mb-3">
                        Guest will receive a <strong>40% refund</strong> ({{ money($booking->grand_total * 0.40) }}).
                        Hotel retains <strong>60%</strong> ({{ money($booking->grand_total * 0.60) }}).
                        <br>This requires <strong>owner approval</strong> before it can be executed.
                    </p>
                    <form method="POST" action="{{ route('receptionist.cancellation-approvals.request', $booking) }}">
                        @csrf
                        <textarea name="reason" rows="3" required minlength="20"
                                  placeholder="Describe the emergency situation (minimum 20 characters)…"
                                  class="form-input w-full text-sm mb-2 resize-none"></textarea>
                        <button class="btn-danger w-full btn-sm">
                            Submit Request to Owner
                        </button>
                    </form>
                </div>
            </div>

            @elseif($cancellationApproval->isPending())
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 p-3 text-xs">
                <p class="font-semibold text-amber-700 dark:text-amber-300">⏳ Awaiting Owner Approval</p>
                <p class="text-amber-600 dark:text-amber-400 mt-1">Request submitted {{ $cancellationApproval->created_at->diffForHumans() }}. Owner must approve before you can proceed.</p>
            </div>

            @elseif($cancellationApproval->isApproved())
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800 p-3">
                <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-300 mb-1">✓ Owner Approved — Ready to Execute</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mb-2">
                    Refund: {{ money($cancellationApproval->refund_amount) }} (40%) &nbsp;|&nbsp;
                    Deducted: {{ money($cancellationApproval->deduction_amount) }} (60%)
                </p>
                <form method="POST" action="{{ route('receptionist.cancellation-approvals.execute', $cancellationApproval) }}"
                      onsubmit="return confirm('Execute emergency cancellation? Booking will be cancelled with 40% refund. This cannot be undone.')">
                    @csrf
                    <button class="btn-danger w-full btn-sm">Execute Emergency Cancellation</button>
                </form>
            </div>

            @elseif($cancellationApproval->isDenied())
            <div class="rounded-xl bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800 p-3 text-xs">
                <p class="font-semibold text-rose-700 dark:text-rose-400">✗ Request Denied</p>
                @if($cancellationApproval->denial_reason)
                <p class="text-rose-600 dark:text-rose-400 mt-1">Reason: {{ $cancellationApproval->denial_reason }}</p>
                @endif
            </div>
            @endif
            @endif

            <a href="{{ route('receptionist.bookings.invoice', $booking) }}" target="_blank"
               class="btn-ghost w-full block text-center">{{ __('Print Invoice / Receipt') }}</a>
        </div>

        <div class="card p-5 text-sm space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">{{ __('Quick Info') }}</h3>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Hotel') }}</span><span>{{ $hotel->name }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Room Type') }}</span><span>{{ $booking->roomType->name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">{{ __('Booked') }}</span><span>{{ $booking->created_at->diffForHumans() }}</span></div>
        </div>
    </div>
</div>
@endsection
