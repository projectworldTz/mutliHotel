@extends('layouts.owner')
@section('title', 'Guest Surveys — ' . $hotel->name)
@section('page-title', 'Guest Satisfaction Surveys')

@section('content')

<div class="mb-5">
    <a href="{{ route('owner.hotels.show', $hotel) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
        ← {{ $hotel->name }}
    </a>
    <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Guest Satisfaction Surveys</h2>
    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Auto-sent to guests a few hours after checkout.</p>
</div>

<div class="grid gap-4 sm:grid-cols-3 mb-6">
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Average Rating</p>
        <p class="text-2xl font-bold text-gold">{{ $summary['average_rating'] }} <span class="text-sm font-normal text-slate-400">/ 5</span></p>
    </div>
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Response Rate</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $summary['response_rate'] }}%</p>
    </div>
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-4">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Surveys Sent</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $summary['sent'] }}</p>
    </div>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>Booking</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Responded</th>
                </tr>
            </thead>
            <tbody>
                @forelse($surveys as $survey)
                <tr class="tr-hover">
                    <td class="font-medium text-slate-900 dark:text-white">{{ $survey->user->name ?? '—' }}</td>
                    <td class="text-sm text-slate-500">{{ $survey->booking->booking_number ?? '—' }}</td>
                    <td>
                        @if($survey->rating)
                        <span class="text-gold">{{ str_repeat('★', $survey->rating) }}{{ str_repeat('☆', 5 - $survey->rating) }}</span>
                        @else
                        <span class="text-slate-300 dark:text-slate-600">Pending</span>
                        @endif
                    </td>
                    <td class="text-sm text-slate-600 dark:text-slate-300 max-w-xs truncate" title="{{ $survey->comment }}">{{ $survey->comment ?? '—' }}</td>
                    <td class="text-sm text-slate-500">{{ $survey->responded_at?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-14 text-center text-slate-500">No surveys sent yet — they go out automatically a few hours after a guest checks out.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($surveys->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $surveys->links() }}</div>
    @endif
</div>

@endsection
