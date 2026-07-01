@extends('layouts.admin')
@section('title', 'Booking #' . $booking->booking_number)
@section('page-title', __('Booking Detail'))

@section('content')
<div class="mb-4"><a href="{{ route('admin.bookings.index') }}" class="btn-ghost btn-sm">{{ __('← Back to Bookings') }}</a></div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-5">
        <div class="card p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('Booking #') }}{{ $booking->booking_number }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">{{ __('Created') }} {{ $booking->created_at->format('d M Y, H:i') }}</p>
                </div>
                <span class="badge badge-{{ $booking->status }}">{{ ucfirst(str_replace('_',' ',$booking->status)) }}</span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 text-sm">
                <div><p class="form-label mb-0.5">{{ __('Guest') }}</p><p class="font-medium">{{ $booking->user->name ?? 'N/A' }}</p><p class="text-slate-500">{{ $booking->user->email ?? '' }}</p></div>
                <div><p class="form-label mb-0.5">{{ __('Hotel') }}</p><p class="font-medium">{{ $booking->hotel->name ?? 'N/A' }}</p><p class="text-slate-500">{{ $booking->hotel->city ?? '' }}</p></div>
                <div><p class="form-label mb-0.5">{{ __('Payment') }}</p><p class="font-medium capitalize">{{ $booking->payment_method ?? 'N/A' }}</p><p class="text-slate-500 capitalize">{{ $booking->payment_status ?? '' }}</p></div>
                <div><p class="form-label mb-0.5">{{ __('Total') }}</p><p class="text-xl font-bold text-navy dark:text-navy-light">{{ money($booking->grand_total ?? 0) }}</p></div>
            </div>

            @if($booking->special_requests)
            <div class="mt-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3">
                <p class="text-xs font-semibold text-slate-500 mb-1">{{ __('Special Requests') }}</p>
                <p class="text-sm">{{ $booking->special_requests }}</p>
            </div>
            @endif
        </div>

        <div class="card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700"><h3 class="font-bold text-slate-900 dark:text-white">{{ __('Booked Rooms') }}</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>{{ __('Room') }}</th><th>{{ __('Check-in') }}</th><th>{{ __('Check-out') }}</th><th>{{ __('Nights') }}</th><th>{{ __('Price') }}</th></tr></thead>
                    <tbody>
                        @foreach($booking->rooms as $item)
                        <tr class="tr-hover">
                            <td class="font-medium">{{ $item->roomType->name ?? 'Room' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->check_in)->format('d M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->check_out)->format('d M Y') }}</td>
                            <td>{{ $item->nights }}</td>
                            <td class="font-semibold">{{ money($item->sub_total ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-5 space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">{{ __('Manage Booking') }}</h3>
            @if($booking->status === 'pending')
            <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">
                @csrf
                <button class="btn-success w-full">{{ __('Confirm Booking') }}</button>
            </form>
            @endif
            @if($booking->status === 'confirmed')
            <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                @csrf
                <button class="btn-primary w-full">{{ __('Check In') }}</button>
            </form>
            @endif
            @if($booking->status === 'checked_in')
            <form method="POST" action="{{ route('admin.bookings.check-out', $booking) }}">
                @csrf
                <button class="btn-outline w-full">{{ __('Check Out') }}</button>
            </form>
            @endif
            @if(!in_array($booking->status, ['cancelled','refunded','checked_out']))
            <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}">
                @csrf
                <button class="btn-danger w-full" onclick="return confirm('{{ __('Cancel this booking?') }}')">{{ __('Cancel') }}</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
