@extends('layouts.receptionist')
@section('title', __('Digital Check-ins'))
@section('page-title', __('Digital Check-ins'))

@section('content')

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>{{ __('Guest') }}</th><th>{{ __('Booking') }}</th><th>{{ __('Arrival Time') }}</th><th>{{ __('Preferences') }}</th><th>{{ __('ID Document') }}</th><th>{{ __('Status') }}</th><th class="w-24"></th></tr>
            </thead>
            <tbody>
                @forelse($checkins as $checkin)
                <tr class="tr-hover">
                    <td class="font-medium text-slate-900 dark:text-white">{{ $checkin->booking->user->name ?? '—' }}</td>
                    <td class="text-sm text-slate-500 font-mono">{{ $checkin->booking->booking_number }}</td>
                    <td class="text-sm">{{ $checkin->estimated_arrival_time ?? '—' }}</td>
                    <td class="text-sm text-slate-600 dark:text-slate-300 max-w-[200px] truncate">{{ $checkin->preferences ?? '—' }}</td>
                    <td>
                        @if($checkin->id_document_path)
                        <a href="{{ $checkin->id_document_url }}" target="_blank" class="text-navy dark:text-gold underline text-sm">{{ __('View') }}</a>
                        @else
                        <span class="text-slate-300 dark:text-slate-600">—</span>
                        @endif
                    </td>
                    <td>
                        @if($checkin->isVerified())
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ __('Verified') }}</span>
                        @else
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">{{ __('Pending') }}</span>
                        @endif
                    </td>
                    <td>
                        @unless($checkin->isVerified())
                        <form method="POST" action="{{ route('receptionist.checkins.verify', $checkin) }}">
                            @csrf
                            <button class="btn-primary btn-sm">{{ __('Verify') }}</button>
                        </form>
                        @endunless
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-14 text-center text-slate-500">{{ __('No check-ins submitted yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($checkins->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $checkins->links() }}</div>
    @endif
</div>

@endsection
