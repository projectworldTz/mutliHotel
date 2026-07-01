@extends('layouts.receptionist')
@section('title', __('Bookings'))
@section('page-title', __('Bookings'))

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    {{-- Status tabs --}}
    <div class="flex flex-wrap gap-2">
        @foreach(['all' => __('All'), 'pending' => __('Pending'), 'confirmed' => __('Confirmed'), 'checked_in' => __('In-House'), 'checked_out' => __('Checked Out'), 'cancelled' => __('Cancelled')] as $val => $label)
        <a href="{{ route('receptionist.bookings.index', ['status' => $val, 'search' => $search]) }}"
           class="{{ $status === $val ? 'btn-primary btn-sm' : 'btn-ghost btn-sm' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="flex flex-wrap gap-2">
        <form method="GET" action="{{ route('receptionist.bookings.index') }}" class="flex flex-wrap gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('Search name / booking #') }}"
                   data-live-search
                   class="form-input w-full sm:w-52">
            <button type="submit" class="btn-outline btn-sm">{{ __('Search') }}</button>
        </form>
        <a href="{{ route('receptionist.bookings.create') }}" class="btn-gold btn-sm">+ {{ __('New Booking') }}</a>
    </div>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Booking #') }}</th>
                <th>{{ __('Guest') }}</th>
                <th>{{ __('Room Type') }}</th>
                <th>{{ __('Check-in') }}</th>
                <th>{{ __('Check-out') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Total') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
            <tr class="tr-hover">
                <td class="font-mono text-xs font-semibold">{{ $booking->booking_number }}</td>
                <td>
                    <p class="font-medium text-slate-900 dark:text-white">{{ $booking->user->name }}</p>
                    <p class="text-xs text-slate-500">{{ $booking->user->email }}</p>
                </td>
                <td>{{ $booking->roomType->name ?? '—' }}</td>
                <td>{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}</td>
                <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span></td>
                <td class="font-semibold">{{ money($booking->grand_total) }}</td>
                <td>
                    <a href="{{ route('receptionist.bookings.show', $booking) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="py-10 text-center text-slate-500">{{ __('No bookings found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
