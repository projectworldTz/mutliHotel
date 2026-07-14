@extends('layouts.owner')
@section('title', $campaign->subject . ' — ' . $hotel->name)
@section('page-title', 'Campaign')

@section('content')

<div class="mb-4"><a href="{{ route('owner.campaigns.index', $hotel) }}" class="btn-ghost btn-sm">← Campaigns</a></div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $campaign->subject }}</h2>
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $campaign->status === 'sent' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                {{ ucfirst($campaign->status) }}
            </span>
        </div>
        <p class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $campaign->body }}</p>
    </div>

    <div class="space-y-4">
        <div class="card p-5 space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Audience</span><span class="font-medium">{{ $campaign->audience_label }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Recipients</span><span class="font-medium">{{ $campaign->recipient_count }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Sent</span><span class="font-medium">{{ $campaign->sent_at?->format('d M Y H:i') ?? '—' }}</span></div>
        </div>

        @if($campaign->status === 'draft')
        <form method="POST" action="{{ route('owner.campaigns.send', [$hotel, $campaign]) }}"
              onsubmit="return confirm('Send this campaign now? This cannot be undone.')">
            @csrf
            <button type="submit" class="btn-primary w-full">Send Now</button>
        </form>
        @endif
    </div>
</div>

@endsection
