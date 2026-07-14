@extends('layouts.app')
@section('title', __('Booking #') . $booking->booking_number)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Payment pending banner with live polling --}}
    @if(session('payment_pending') && $booking->payment && $booking->payment->status === 'pending')
    <div id="payment-pending-banner"
         class="mb-6 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-6">
        <div class="flex items-start gap-4">
            <div class="shrink-0 mt-0.5">
                <svg class="h-8 w-8 text-amber-500 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-amber-900 dark:text-amber-100">{{ __('Payment Request Sent') }}</h2>
                <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">
                    {{ session('payment_message') }}
                </p>
                <div class="mt-3 flex items-center gap-2 text-xs text-amber-700 dark:text-amber-300">
                    <svg class="h-4 w-4 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span id="polling-status">{{ __('Waiting for payment confirmation — checking every 5 seconds…') }}</span>
                </div>

                @if(app()->environment(['local', 'staging']))
                <div class="mt-4 pt-4 border-t border-amber-200 dark:border-amber-700">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-300 mb-2 uppercase tracking-wide">
                        ⚙ {{ __('Development Only') }} — {{ __('Simulate Payment Confirmation') }}
                    </p>
                    <form method="POST" action="{{ route('dev.payment.confirm', $booking->payment->id) }}"
                          data-loading data-confirm="{{ __('Simulate payment confirmation?') }}">
                        @csrf
                        <button type="submit" class="btn-outline btn-sm text-xs">
                            {{ __('Confirm Payment (Test)') }}
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        pollPaymentStatus(
            {{ $booking->payment->id }},
            '{{ route('booking.show', $booking->booking_number) }}',
            '{{ csrf_token() }}'
        );
    });
    </script>
    @endpush
    @elseif(session('manual_payment') && $booking->payment && $booking->payment->status === 'pending')
    <div class="mb-6 rounded-2xl bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-800 p-6">
        <div class="flex items-start gap-4">
            <div class="shrink-0 mt-0.5">
                <svg class="h-8 w-8 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-sky-900 dark:text-sky-100">{{ __('Online Payment Unavailable') }}</h2>
                <p class="mt-1 text-sm text-sky-800 dark:text-sky-200">
                    {{ __('Online payment is currently not working for this hotel. Please make your payment using one of the numbers below, then contact the hotel to confirm your booking.') }}
                </p>

                @php $numbers = $booking->hotel?->manualPaymentNumbers() ?? []; @endphp
                @if(!empty($numbers))
                @php $labels = ['airtel_money' => 'Airtel Money', 'mpesa' => 'M-Pesa', 'halotel' => 'Halotel', 'mix_by_yas' => 'Mix by Yas']; @endphp
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach($numbers as $key => $entry)
                    <div class="rounded-lg bg-white/60 dark:bg-slate-800/60 px-3 py-2">
                        <p class="text-xs text-sky-700 dark:text-sky-300">{{ $labels[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}</p>
                        <p class="font-mono font-semibold text-sky-900 dark:text-sky-100">{{ $entry['number'] }}</p>
                        @if(!empty($entry['name']))
                        <p class="text-xs text-sky-700 dark:text-sky-300">{{ __('Name') }}: {{ $entry['name'] }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                @if($booking->hotel?->phone || $booking->hotel?->email)
                <p class="mt-4 text-sm text-sky-800 dark:text-sky-200">
                    {{ __('Contact the hotel to confirm your booking:') }}
                    @if($booking->hotel->phone) <strong>{{ $booking->hotel->phone }}</strong> @endif
                    @if($booking->hotel->email) &middot; <strong>{{ $booking->hotel->email }}</strong> @endif
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Booking confirmed success banner --}}
    @if(session('success') && !session('payment_pending'))
    <div class="mb-6 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h2 class="text-xl font-bold text-emerald-800 dark:text-emerald-200">{{ __('Booking Confirmed!') }}</h2>
        <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
            {{ session('success') }}
        </p>
    </div>
    @endif

    <div class="card p-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    {{ __('Booking #') }}{{ $booking->booking_number }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Placed') }} {{ $booking->created_at->format('d M Y, H:i') }}
                </p>
            </div>
            <span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
        </div>

        {{-- Hotel info --}}
        <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4 mb-5">
            <h3 class="font-semibold text-slate-900 dark:text-white">{{ $booking->hotel->name }}</h3>
            <p class="text-sm text-slate-500">{{ $booking->hotel->city }}, {{ $booking->hotel->country }}</p>
        </div>

        {{-- Booking items --}}
        <div class="space-y-3 mb-5">
            @foreach($booking->rooms as $item)
            <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                <div class="flex justify-between">
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $item->roomType->name ?? __('Room') }}</p>
                        <div class="mt-1 text-sm text-slate-500">
                            <span>{{ \Carbon\Carbon::parse($item->check_in)->format('D, d M Y') }}</span>
                            <span class="mx-1">→</span>
                            <span>{{ \Carbon\Carbon::parse($item->check_out)->format('D, d M Y') }}</span>
                            <span class="ml-2">({{ $item->nights }} {{ __('nights') }})</span>
                        </div>
                    </div>
                    <p class="font-bold text-slate-900 dark:text-white">{{ money($item->sub_total ?? 0) }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Meal Packages & Add-ons --}}
        @if($booking->mealPackages->isNotEmpty())
        <div class="space-y-2 mb-5">
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Meal Packages & Add-ons') }}</p>
            @foreach($booking->mealPackages as $mp)
            <div class="flex justify-between text-sm text-slate-600 dark:text-slate-300">
                <span>{{ $mp->name }} @if($mp->quantity > 1) × {{ $mp->quantity }} @endif</span>
                <span>{{ money($mp->sub_total) }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Totals --}}
        <div class="border-t border-slate-100 dark:border-slate-700 pt-4 space-y-1.5 text-sm mb-5">
            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                <span>{{ __('Subtotal') }}</span>
                <span>{{ money($booking->sub_total ?? 0) }}</span>
            </div>
            @if(($booking->addons_total ?? 0) > 0)
            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                <span>{{ __('Add-ons') }}</span>
                <span>{{ money($booking->addons_total) }}</span>
            </div>
            @endif
            @if(($booking->discount_total ?? 0) > 0)
            <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                <span>{{ __('Discount') }}</span>
                <span>−{{ money($booking->discount_total) }}</span>
            </div>
            @endif
            @if(($booking->tax_total ?? 0) > 0)
            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                <span>{{ __('Tax') }} ({{ $booking->tax_rate }}%)</span>
                <span>{{ money($booking->tax_total ?? 0) }}</span>
            </div>
            @endif
            <div class="flex justify-between font-bold text-base text-slate-900 dark:text-white pt-1">
                <span>{{ __('Total') }}</span>
                <span>{{ money($booking->grand_total ?? 0) }}</span>
            </div>
        </div>

        {{-- Payment info --}}
        <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4 mb-5 text-sm">
            <p class="font-semibold text-slate-900 dark:text-white mb-2">{{ __('Payment') }}</p>
            @if($booking->payment)
            <div class="space-y-1">
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">{{ __('Method') }}</span>
                    <span class="font-medium text-slate-900 dark:text-white">{{ $booking->payment->method_label }}</span>
                </div>
                @if($booking->payment->metadata['phone'] ?? null)
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">{{ __('Phone') }}</span>
                    <span class="font-medium text-slate-900 dark:text-white font-mono">+255{{ $booking->payment->metadata['phone'] }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-slate-500 dark:text-slate-400">{{ __('Status') }}</span>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full
                        {{ $booking->payment->status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : '' }}
                        {{ $booking->payment->status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : '' }}
                        {{ $booking->payment->status === 'failed' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' : '' }}
                    ">
                        {{ ucfirst($booking->payment->status) }}
                    </span>
                </div>
            </div>
            @else
            <p class="text-slate-500 dark:text-slate-400">{{ __('No payment record found.') }}</p>
            @endif
        </div>

        {{-- Refund / cancellation result info --}}
        @if($booking->status === 'cancelled')
        @php $refund = $booking->refund_amount; @endphp
        <div class="rounded-xl border mb-5 p-4 text-sm
            {{ $refund > 0 ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700' : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-700' }}">
            <p class="font-semibold mb-1 {{ $refund > 0 ? 'text-emerald-800 dark:text-emerald-200' : 'text-slate-700 dark:text-slate-300' }}">
                {{ $refund > 0 ? __('Refund Approved') : __('No Refund') }}
            </p>
            @if($refund > 0)
            <p class="text-emerald-700 dark:text-emerald-300">
                {{ __('A refund of') }} <strong>{{ $booking->currency }} {{ number_format($refund, 2) }}</strong>
                {{ __('will be transferred to your') }} <strong>{{ $booking->payment?->method_label }}</strong>
                {{ __('within 2–3 business days.') }}
            </p>
            @elseif($booking->payment && $booking->payment->status === 'paid')
            <p class="text-slate-600 dark:text-slate-400">
                {{ __('This booking was cancelled within 24 hours of check-in and is not eligible for a refund per our cancellation policy.') }}
            </p>
            @else
            <p class="text-slate-600 dark:text-slate-400">
                {{ __('No charge was made for this booking since payment had not been confirmed at the time of cancellation.') }}
            </p>
            @endif
            @if($booking->cancellation_reason)
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                <strong>{{ __('Reason:') }}</strong> {{ $booking->cancellation_reason }}
            </p>
            @endif
        </div>
        @endif

        {{-- Cancellation policy notice (only shown when booking can still be cancelled) --}}
        @if($booking->is_cancellable)
        @php $policy = $booking->policy_snapshot; @endphp
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 p-4 mb-5 text-sm">
            <p class="font-semibold text-blue-800 dark:text-blue-200 mb-1">{{ __('Cancellation Policy') }}</p>
            <ul class="text-blue-700 dark:text-blue-300 space-y-0.5 list-disc list-inside text-xs">
                <li>{{ __('Cancel 48+ hours before check-in → 100% refund') }}</li>
                <li>{{ __('Cancel 24–48 hours before check-in → 50% refund') }}</li>
                <li>{{ __('Cancel less than 24 hours before check-in → No refund') }}</li>
            </ul>
        </div>
        @endif

        {{-- Actions --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('booking.invoice', $booking->booking_number) }}"
               class="btn-outline btn-sm">
                {{ __('Download Invoice') }}
            </a>
            @if($booking->is_cancellable)
            <form method="POST" action="{{ route('booking.cancel', $booking->booking_number) }}"
                  data-loading data-confirm="{{ __('Are you sure you want to cancel this booking? This action cannot be undone.') }}">
                @csrf
                <button type="submit" class="btn-danger btn-sm">
                    {{ __('Cancel Booking') }}
                </button>
            </form>
            @endif
            <a href="{{ route('account.bookings') }}" class="btn-ghost btn-sm ml-auto">{{ __('← My Bookings') }}</a>
        </div>
    </div>
</div>
@endsection
