@extends('layouts.app')
@section('title', 'Booking #' . $booking->booking_number)

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Success banner for new bookings --}}
    @if(session('booking_success'))
    <div class="mb-6 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-6 text-center">
        <svg class="mx-auto h-12 w-12 text-emerald-500 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h2 class="text-xl font-bold text-emerald-800 dark:text-emerald-200">Booking Confirmed!</h2>
        <p class="mt-1 text-sm text-emerald-700 dark:text-emerald-300">
            Your reservation has been received. A confirmation email has been sent to your inbox.
        </p>
    </div>
    @endif

    {{-- Bank transfer instructions --}}
    @if(session('bank_details'))
    @php $bank = session('bank_details'); @endphp
    <div class="mb-6 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-6">
        <div class="flex items-start gap-3">
            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-bold text-blue-900 dark:text-blue-200 mb-3">Bank Transfer Instructions</h3>
                <p class="text-sm text-blue-800 dark:text-blue-300 mb-4">
                    Please transfer <strong>TZS {{ number_format($booking->grand_total, 0) }}</strong>
                    to the account below. Use your booking number as the payment reference.
                </p>
                <div class="grid gap-2 sm:grid-cols-2 text-sm">
                    @foreach([
                        ['Account Name',   $bank['account_name']   ?? ''],
                        ['Account Number', $bank['account_number'] ?? ''],
                        ['Bank Name',      $bank['bank_name']      ?? ''],
                        ['SWIFT / BIC',    $bank['swift_code']     ?? ''],
                        ['Reference',      $bank['reference']      ?? $booking->booking_number],
                    ] as [$label, $val])
                    @if($val)
                    <div class="rounded-lg bg-white dark:bg-slate-800 border border-blue-200 dark:border-blue-700 px-3 py-2">
                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">{{ $label }}</p>
                        <p class="font-bold text-slate-900 dark:text-white font-mono">{{ $val }}</p>
                    </div>
                    @endif
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-blue-700 dark:text-blue-400">
                    Your booking will be confirmed once the transfer is received (1–3 business days).
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="card p-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                    Booking #{{ $booking->booking_number }}
                </h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                    Placed {{ $booking->created_at->format('d M Y, H:i') }}
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
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $item->roomType->name ?? 'Room' }}</p>
                        <div class="mt-1 text-sm text-slate-500">
                            <span>{{ \Carbon\Carbon::parse($item->check_in)->format('D, d M Y') }}</span>
                            <span class="mx-1">→</span>
                            <span>{{ \Carbon\Carbon::parse($item->check_out)->format('D, d M Y') }}</span>
                            <span class="ml-2">({{ $item->nights }} nights)</span>
                        </div>
                    </div>
                    <p class="font-bold text-slate-900 dark:text-white">TZS {{ number_format($item->sub_total ?? 0, 0) }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div class="border-t border-slate-100 dark:border-slate-700 pt-4 space-y-1.5 text-sm mb-5">
            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                <span>Subtotal</span>
                <span>TZS {{ number_format($booking->sub_total ?? 0, 0) }}</span>
            </div>
            @if(($booking->discount_total ?? 0) > 0)
            <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                <span>Discount</span>
                <span>−TZS {{ number_format($booking->discount_total, 0) }}</span>
            </div>
            @endif
            <div class="flex justify-between text-slate-600 dark:text-slate-300">
                <span>Tax ({{ $booking->tax_rate }}%)</span>
                <span>TZS {{ number_format($booking->tax_total ?? 0, 0) }}</span>
            </div>
            <div class="flex justify-between font-bold text-base text-slate-900 dark:text-white pt-1">
                <span>Total</span>
                <span>TZS {{ number_format($booking->grand_total ?? 0, 0) }}</span>
            </div>
        </div>

        {{-- Payment info --}}
        <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-4 mb-5 text-sm">
            <p class="font-semibold text-slate-900 dark:text-white mb-1">Payment</p>
            <p class="text-slate-600 dark:text-slate-300">
                Method: <span class="capitalize">{{ $booking->payment_method ?? 'N/A' }}</span>
            </p>
            <p class="text-slate-600 dark:text-slate-300">
                Status: <span class="capitalize">{{ $booking->payment_status ?? 'pending' }}</span>
            </p>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('booking.invoice', $booking->booking_number) }}"
               class="btn-outline btn-sm">
                Download Invoice
            </a>
            @if(in_array($booking->status, ['pending', 'confirmed']))
            <form method="POST" action="{{ route('booking.cancel', $booking->booking_number) }}">
                @csrf
                <button type="submit"
                        onclick="return confirm('Are you sure you want to cancel this booking?')"
                        class="btn-danger btn-sm">
                    Cancel Booking
                </button>
            </form>
            @endif
            <a href="{{ route('account.bookings') }}" class="btn-ghost btn-sm ml-auto">← My Bookings</a>
        </div>
    </div>
</div>
@endsection
