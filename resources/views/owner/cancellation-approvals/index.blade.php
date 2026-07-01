@extends('layouts.owner')
@section('title', 'Cancellation Approvals — ' . $hotel->name)
@section('page-title', 'Cancellation Approvals')

@section('content')

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('owner.hotels.show', $hotel) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            ← {{ $hotel->name }}
        </a>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Emergency Cancellation Approvals</h2>
        @if($pendingCount > 0)
        <p class="text-sm text-amber-600 dark:text-amber-400 font-medium mt-1">
            ⚠ {{ $pendingCount }} request{{ $pendingCount > 1 ? 's' : '' }} awaiting your decision
        </p>
        @endif
    </div>
</div>

@if(session('success'))
<div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 flex gap-3">
    <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</p>
</div>
@endif

<div class="space-y-4">
    @forelse($approvals as $approval)
    @php $booking = $approval->booking; @endphp
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">

        {{-- Header row --}}
        <div class="flex flex-wrap items-start justify-between gap-4 p-5 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-start gap-4">
                {{-- Status indicator --}}
                <div class="rounded-xl p-2.5 {{ $approval->isPending() ? 'bg-amber-50 dark:bg-amber-900/20' : ($approval->isApproved() ? 'bg-emerald-50 dark:bg-emerald-900/20' : ($approval->isDenied() ? 'bg-rose-50 dark:bg-rose-900/20' : 'bg-slate-100 dark:bg-slate-700')) }}">
                    @if($approval->isPending())
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($approval->isApproved())
                    <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($approval->isDenied())
                    <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-slate-900 dark:text-white font-mono">{{ $booking->booking_number }}</span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $approval->statusColor() }}">
                            {{ ucfirst($approval->status) }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Requested by <span class="font-medium text-slate-700 dark:text-slate-300">{{ $approval->requestedBy->name }}</span>
                        · {{ $approval->created_at->format('d M Y, H:i') }}
                    </p>
                </div>
            </div>

            {{-- Financial breakdown — only when relevant --}}
            @if($approval->isDenied())
            <div class="text-center">
                <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Total Paid</p>
                <p class="font-bold text-slate-900 dark:text-white">{{ money($approval->total_paid) }}</p>
                <p class="text-xs text-rose-400 mt-1 font-medium">No deduction — request denied</p>
            </div>
            @else
            <div class="flex items-center gap-6 text-sm">
                <div class="text-center">
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Total Paid</p>
                    <p class="font-bold text-slate-900 dark:text-white">{{ money($approval->total_paid) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-rose-400 uppercase tracking-wide mb-0.5">Deducted (60%)</p>
                    <p class="font-bold text-rose-600 dark:text-rose-400">{{ money($approval->deduction_amount) }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-emerald-500 uppercase tracking-wide mb-0.5">Refund (40%)</p>
                    <p class="font-bold text-emerald-600 dark:text-emerald-400">{{ money($approval->refund_amount) }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Booking + Guest details --}}
        <div class="grid sm:grid-cols-2 gap-4 p-5 text-sm border-b border-slate-100 dark:border-slate-700">
            <div class="space-y-1.5">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Booking Details</h4>
                <div class="flex justify-between"><span class="text-slate-500">Guest</span><span class="font-medium text-slate-900 dark:text-white">{{ $booking->guest_name ?? $booking->user?->name ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Check-in</span><span>{{ $booking->check_in->format('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Check-out</span><span>{{ $booking->check_out->format('d M Y') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Nights</span><span>{{ $booking->nights }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Booking Status</span>
                    <span class="capitalize">{{ str_replace('_', ' ', $booking->status) }}</span>
                </div>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Emergency Reason</h4>
                <p class="text-slate-700 dark:text-slate-300 leading-relaxed text-sm bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-xl p-3">
                    {{ $approval->reason }}
                </p>
                @if($approval->isDenied() && $approval->denial_reason)
                <div class="mt-2">
                    <h4 class="text-xs font-bold text-rose-400 uppercase tracking-wider mb-1">Denial Reason</h4>
                    <p class="text-sm text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800 rounded-xl p-3">
                        {{ $approval->denial_reason }}
                    </p>
                </div>
                @endif
                @if($approval->isApproved())
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
                    ✓ Approved {{ $approval->approved_at->format('d M Y H:i') }} — receptionist can now execute.
                </p>
                @endif
                @if($approval->isExecuted())
                <p class="text-xs text-slate-400 mt-2">
                    ✓ Executed {{ $approval->executed_at->format('d M Y H:i') }}
                </p>
                @endif
            </div>
        </div>

        {{-- Actions (only for pending) --}}
        @if($approval->isPending())
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex flex-wrap gap-3 items-start" x-data="{ denying: false }">
            {{-- Approve --}}
            <form method="POST" action="{{ route('owner.cancellation-approvals.approve', [$hotel, $approval]) }}"
                  onsubmit="return confirm('Approve this cancellation? The receptionist will be able to execute it immediately.')">
                @csrf
                <button class="btn-primary flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Approve (Grant Permission)
                </button>
            </form>

            {{-- Deny with reason --}}
            <button @click="denying = !denying" class="btn-outline flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Deny
            </button>

            <form x-show="denying" method="POST" action="{{ route('owner.cancellation-approvals.deny', [$hotel, $approval]) }}"
                  class="w-full flex flex-col sm:flex-row gap-2 mt-1" style="display:none">
                @csrf
                <input type="text" name="denial_reason" required
                       placeholder="Reason for denial (required)…"
                       class="form-input flex-1 text-sm">
                <button class="btn-outline text-rose-600 border-rose-300 hover:bg-rose-50 whitespace-nowrap">
                    Confirm Denial
                </button>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-slate-500 font-medium">No cancellation requests for this hotel.</p>
    </div>
    @endforelse
    @if($approvals->hasPages())
    <div class="p-4">{{ $approvals->links() }}</div>
    @endif
</div>

@endsection
