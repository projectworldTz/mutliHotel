@extends('layouts.receptionist')
@section('title', __('Guest Messages'))
@section('page-title', __('Guest Messages'))

@section('content')

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>{{ __('Guest') }}</th><th>{{ __('Booking') }}</th><th>{{ __('Last Message') }}</th><th>{{ __('Unread') }}</th><th class="w-20"></th></tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                @php $last = $booking->messages->first(); @endphp
                <tr class="tr-hover">
                    <td class="font-medium text-slate-900 dark:text-white">{{ $booking->user->name ?? '—' }}</td>
                    <td class="text-sm text-slate-500 font-mono">{{ $booking->booking_number }}</td>
                    <td class="text-sm text-slate-600 dark:text-slate-300 max-w-[260px] truncate">{{ $last->message ?? '—' }}</td>
                    <td>
                        @if($booking->unread_count > 0)
                        <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1.5 text-[10px] font-bold text-white">{{ $booking->unread_count }}</span>
                        @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td><a href="{{ route('receptionist.messages.show', $booking) }}" class="btn-ghost btn-sm">{{ __('Open') }}</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-14 text-center text-slate-500">{{ __('No guest messages yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
