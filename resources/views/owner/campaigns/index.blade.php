@extends('layouts.owner')
@section('title', 'Email Marketing — ' . $hotel->name)
@section('page-title', 'Email Marketing')

@section('content')

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('owner.hotels.show', $hotel) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            ← {{ $hotel->name }}
        </a>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Email Marketing</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Send promotions and newsletters to past and upcoming guests who've opted in.</p>
    </div>
    <a href="{{ route('owner.campaigns.create', $hotel) }}" class="btn-primary flex items-center gap-2">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        New Campaign
    </a>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Audience</th>
                    <th>Status</th>
                    <th>Recipients</th>
                    <th>Sent</th>
                    <th class="w-24"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                <tr class="tr-hover">
                    <td class="font-medium text-slate-900 dark:text-white">{{ $campaign->subject }}</td>
                    <td class="text-sm text-slate-600 dark:text-slate-300">{{ $campaign->audience_label }}</td>
                    <td>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $campaign->status === 'sent' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    </td>
                    <td class="text-sm text-slate-600 dark:text-slate-300">{{ $campaign->recipient_count }}</td>
                    <td class="text-sm text-slate-500">{{ $campaign->sent_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('owner.campaigns.show', [$hotel, $campaign]) }}" class="btn-ghost btn-sm">View</a>
                            @if($campaign->status === 'draft')
                            <form method="POST" action="{{ route('owner.campaigns.destroy', [$hotel, $campaign]) }}"
                                  onsubmit="return confirm('Delete this draft?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-14 text-center text-slate-500">No campaigns yet. <a href="{{ route('owner.campaigns.create', $hotel) }}" class="text-navy dark:text-gold underline">Create one</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($campaigns->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $campaigns->links() }}</div>
    @endif
</div>

@endsection
