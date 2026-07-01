@extends('layouts.owner')
@section('title', __('Bookings') . ' — ' . $hotel->name)
@section('page-title', $hotel->name . ' — ' . __('Bookings'))

@section('content')
<div class="mb-4"><a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">{{ __('← Back to Hotel') }}</a></div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Booking #') }}</th>
                <th>{{ __('Guest') }}</th>
                <th>{{ __('Check-in') }}</th>
                <th>{{ __('Check-out') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $b)
            <tr class="tr-hover">
                <td class="font-mono text-xs">{{ $b->booking_number }}</td>
                <td>{{ $b->user->name ?? 'N/A' }}</td>
                <td class="whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($b->check_in)->format('d M Y') }}</td>
                <td class="whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($b->check_out)->format('d M Y') }}</td>
                <td class="font-semibold">{{ money($b->grand_total ?? 0) }}</td>
                <td><span class="badge badge-{{ $b->status }}">{{ ucfirst(str_replace('_',' ',$b->status)) }}</span></td>
                <td><a href="{{ route('owner.hotels.bookings.show', [$hotel, $b]) }}" class="btn-ghost btn-sm">{{ __('View') }}</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-10 text-slate-500">{{ __('No bookings found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $bookings->links() }}</div>
@endsection
