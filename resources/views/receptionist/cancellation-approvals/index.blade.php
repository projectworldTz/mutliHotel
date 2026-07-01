@extends('layouts.receptionist')
@section('title', 'Emergency Cancellations')
@section('page-title', 'Emergency Cancellations')

@section('content')

<div class="mb-5">
    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Emergency Cancellation Requests</h2>
    <p class="text-sm text-slate-500 mt-1">Requests you submitted for owner approval. Once approved, you can execute the cancellation.</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 flex gap-3">
    <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</p>
</div>
@endif

<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Guest</th>
                    <th>Total Paid</th>
                    <th>Refund (40%)</th>
                    <th>Deducted (60%)</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th class="w-32">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvals as $approval)
                @php $booking = $approval->booking; @endphp
                <tr class="tr-hover">
                    <td class="font-mono text-xs font-semibold text-slate-700 dark:text-slate-200">
                        {{ $booking->booking_number }}
                    </td>
                    <td class="text-sm">{{ $booking->user?->name ?? '—' }}</td>
                    <td class="text-sm font-medium">{{ money($approval->total_paid) }}</td>
                    <td class="text-sm font-semibold {{ $approval->isDenied() ? 'text-slate-400 line-through' : 'text-emerald-600 dark:text-emerald-400' }}">
                        @if($approval->isDenied()) — @else {{ money($approval->refund_amount) }} @endif
                    </td>
                    <td class="text-sm font-semibold {{ $approval->isDenied() ? 'text-slate-400 line-through' : 'text-rose-600 dark:text-rose-400' }}">
                        @if($approval->isDenied()) — @else {{ money($approval->deduction_amount) }} @endif
                    </td>
                    <td>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $approval->statusColor() }}">
                            {{ ucfirst($approval->status) }}
                        </span>
                        @if($approval->isDenied() && $approval->denial_reason)
                        <p class="text-xs text-rose-500 mt-1 max-w-[180px]" title="{{ $approval->denial_reason }}">
                            {{ Str::limit($approval->denial_reason, 60) }}
                        </p>
                        @endif
                    </td>
                    <td class="text-xs text-slate-400">{{ $approval->created_at->diffForHumans() }}</td>
                    <td>
                        @if($approval->isApproved())
                        <form method="POST" action="{{ route('receptionist.cancellation-approvals.execute', $approval) }}"
                              onsubmit="return confirm('Execute emergency cancellation for booking {{ $booking->booking_number }}? This cannot be undone.')">
                            @csrf
                            <button class="btn-primary btn-sm w-full">
                                Execute Cancel
                            </button>
                        </form>
                        @elseif($approval->isPending())
                        <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">Awaiting owner…</span>
                        @elseif($approval->isExecuted())
                        <span class="text-xs text-slate-400">Done</span>
                        @else
                        <span class="text-xs text-rose-400">Denied</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-12 text-center text-slate-400">
                        <svg class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        No cancellation requests yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($approvals->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $approvals->links() }}</div>
    @endif
</div>

@endsection
