@extends('layouts.admin')
@section('title', __('Bookings'))
@section('page-title', __('All Bookings'))

@section('content')
<div class="mb-5 flex flex-wrap items-center gap-3">
    <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               data-live-search
               class="form-input w-48 py-2 text-sm" placeholder="{{ __('Booking # or guest…') }}">
        <select name="status" class="form-select py-2 text-sm w-auto">
            <option value="">{{ __('All Status') }}</option>
            @foreach(['pending','confirmed','checked_in','checked_out','cancelled','refunded'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="hotel_id" class="form-select py-2 text-sm w-auto">
            <option value="">{{ __('All Hotels') }}</option>
            @foreach($hotels as $h)
            <option value="{{ $h->id }}" {{ (string) request('hotel_id') === (string) $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input py-2 text-sm w-auto" placeholder="{{ __('From') }}">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input py-2 text-sm w-auto" placeholder="{{ __('To') }}">
        <button type="submit" class="btn-primary btn-sm">{{ __('Filter') }}</button>
        <a href="{{ route('admin.bookings.index') }}" class="btn-ghost btn-sm">{{ __('Reset') }}</a>
    </form>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Booking #') }}</th>
                <th>{{ __('Guest') }}</th>
                <th>{{ __('Hotel') }}</th>
                <th>{{ __('Check-in') }}</th>
                <th>{{ __('Check-out') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Status') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $b)
            <tr class="tr-hover" data-href="{{ route('admin.bookings.show', $b) }}">
                <td class="font-mono text-xs">{{ $b->booking_number }}</td>
                <td>{{ $b->user->name ?? 'N/A' }}</td>
                <td>
                    @if($b->hotel)
                        <a href="{{ route('admin.hotels.show', $b->hotel) }}" class="text-navy dark:text-amber-400 hover:underline" onclick="event.stopPropagation()">{{ $b->hotel->name }}</a>
                    @else
                        <span class="text-slate-400">N/A</span>
                    @endif
                </td>
                <td class="text-sm whitespace-nowrap">{{ \Carbon\Carbon::parse($b->check_in)->format('d M Y') }}</td>
                <td class="text-sm whitespace-nowrap">{{ \Carbon\Carbon::parse($b->check_out)->format('d M Y') }}</td>
                <td class="font-semibold">{{ money($b->grand_total ?? 0) }}</td>
                <td><span class="badge badge-{{ $b->status }}">{{ ucfirst(str_replace('_',' ',$b->status)) }}</span></td>
                <td><a href="{{ route('admin.bookings.show', $b) }}" class="btn-ghost btn-sm" onclick="event.stopPropagation()">{{ __('View') }}</a></td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-10 text-slate-500">{{ __('No bookings found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $bookings->withQueryString()->links() }}</div>
@endsection
