@extends('layouts.owner')
@section('title', __('Booking #') . $booking->booking_number)
@section('page-title', __('Booking Detail'))

@section('content')
<div class="mb-4"><a href="{{ route('owner.hotels.bookings.index', $hotel) }}" class="btn-ghost btn-sm">{{ __('← Back to Bookings') }}</a></div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 card p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Booking #') }}{{ $booking->booking_number }}</h2>
                <p class="text-sm text-slate-500">{{ $booking->created_at->format('d M Y, H:i') }}</p>
            </div>
            <span class="badge badge-{{ $booking->status }}">{{ ucfirst(str_replace('_',' ',$booking->status)) }}</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 text-sm mb-5">
            <div><p class="form-label mb-0.5">{{ __('Guest') }}</p><p class="font-medium">{{ $booking->user->name ?? 'N/A' }}</p><p class="text-slate-500">{{ $booking->user->email ?? '' }}</p></div>
            <div><p class="form-label mb-0.5">{{ __('Payment') }}</p><p class="font-medium capitalize">{{ $booking->payment_method ?? 'N/A' }}</p><p class="text-slate-500 capitalize">{{ $booking->payment_status ?? '' }}</p></div>
            <div><p class="form-label mb-0.5">{{ __('Guests') }}</p><p>{{ $booking->guests_adults ?? 1 }} {{ __('adults') }} · {{ $booking->guests_children ?? 0 }} {{ __('children') }}</p></div>
            <div><p class="form-label mb-0.5">{{ __('Total') }}</p><p class="text-xl font-bold text-navy dark:text-navy-light">{{ money($booking->grand_total ?? 0) }}</p></div>
        </div>

        @if($booking->special_requests)
        <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3 text-sm">
            <p class="font-semibold mb-1">{{ __('Special Requests') }}</p>
            <p>{{ $booking->special_requests }}</p>
        </div>
        @endif

        @if($booking->mealPackages->isNotEmpty())
        <div class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3 text-sm mt-3">
            <p class="font-semibold mb-2">{{ __('Meal Packages & Add-ons') }}</p>
            <div class="space-y-1">
                @foreach($booking->mealPackages as $mp)
                <div class="flex justify-between">
                    <span>{{ $mp->name }} @if($mp->quantity > 1) × {{ $mp->quantity }} @endif</span>
                    <span class="font-medium">{{ money($mp->sub_total) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="space-y-3">
        <div class="card p-5 space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">{{ __('Actions') }}</h3>
            @if($booking->status === 'pending')
            <form method="POST" action="{{ route('owner.hotels.bookings.confirm', [$hotel, $booking]) }}">
                @csrf
                <button class="btn-success w-full">{{ __('Confirm') }}</button>
            </form>
            @endif
            @if($booking->status === 'confirmed')
            <form method="POST" action="{{ route('owner.hotels.bookings.check-in', [$hotel, $booking]) }}">
                @csrf
                <button class="btn-primary w-full">{{ __('Check In') }}</button>
            </form>
            @endif
            @if($booking->status === 'checked_in')
            <form method="POST" action="{{ route('owner.hotels.bookings.check-out', [$hotel, $booking]) }}">
                @csrf
                <button class="btn-outline w-full">{{ __('Check Out') }}</button>
            </form>
            @endif
            @if($booking->is_cancellable)
            <form method="POST" action="{{ route('owner.hotels.bookings.cancel', [$hotel, $booking]) }}">
                @csrf
                <button class="btn-danger w-full" onclick="return confirm('{{ __('Cancel this booking?') }}')">{{ __('Cancel') }}</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
