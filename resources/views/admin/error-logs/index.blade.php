@extends('layouts.admin')
@section('title', 'Error Logs')
@section('page-title', 'Error Logs')

@section('content')

{{-- Stat tiles --}}
<div class="grid gap-4 sm:grid-cols-3 mb-6">
    <div class="stat-card">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Open</p>
        <p class="mt-2 text-3xl font-bold text-rose-500">{{ $counts['open'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Resolved</p>
        <p class="mt-2 text-3xl font-bold text-emerald-500">{{ $counts['resolved'] }}</p>
    </div>
    <div class="stat-card">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Ignored</p>
        <p class="mt-2 text-3xl font-bold text-slate-400">{{ $counts['ignored'] }}</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="card p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="min-w-52">
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Reference Code</label>
        <input type="text" name="code" value="{{ request('code') }}" placeholder="e.g. ERR-7F3K9Q"
               class="form-input w-full text-sm font-mono uppercase">
    </div>
    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Status</label>
        <select name="status" class="form-input w-full text-sm">
            <option value="">All Statuses</option>
            <option value="open"     @selected(request('status') === 'open')>Open</option>
            <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
            <option value="ignored"  @selected(request('status') === 'ignored')>Ignored</option>
        </select>
    </div>
    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Hotel</label>
        <select name="hotel_id" class="form-input w-full text-sm">
            <option value="">All Hotels</option>
            @foreach($hotels as $h)
            <option value="{{ $h->id }}" @selected(request('hotel_id') == $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-52">
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Exception Type</label>
        <select name="exception_class" class="form-input w-full text-sm">
            <option value="">All Types</option>
            @foreach($exceptionClasses as $c)
            <option value="{{ $c }}" @selected(request('exception_class') === $c)>{{ class_basename($c) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">From</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm w-40">
    </div>
    <div>
        <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">To</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm w-40">
    </div>
    <div class="flex gap-2 self-end">
        <button type="submit" class="btn-primary btn-sm">Search</button>
        @if(request()->hasAny(['code','status','hotel_id','exception_class','date_from','date_to']))
        <a href="{{ route('admin.error-logs.index') }}" class="btn-ghost btn-sm">Clear</a>
        @endif
    </div>
</form>

{{-- Log table --}}
<div class="card">
    <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
        <h3 class="font-bold text-slate-900 dark:text-white">
            Errors
            <span class="ml-2 text-sm font-normal text-slate-500">({{ $logs->total() }} entries)</span>
        </h3>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Status</th>
                    <th>Exception</th>
                    <th>Hotel / User</th>
                    <th>Occurrences</th>
                    <th>Last Seen</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="tr-hover">
                    <td class="font-mono text-xs font-semibold text-slate-900 dark:text-white whitespace-nowrap">{{ $log->code }}</td>
                    <td>
                        @php $color = $log->status_color; @endphp
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                            {{ $color === 'emerald' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' :
                               ($color === 'rose'   ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' :
                                                      'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300') }}">
                            {{ ucfirst($log->status) }}
                        </span>
                    </td>
                    <td class="text-sm max-w-sm">
                        <p class="font-medium text-slate-900 dark:text-white">{{ class_basename($log->exception_class) }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $log->message }}</p>
                    </td>
                    <td class="text-sm">
                        @if($log->hotel)
                            <p class="text-slate-700 dark:text-slate-300">{{ $log->hotel->name }}</p>
                        @else
                            <p class="text-slate-400 text-xs">Platform</p>
                        @endif
                        @if($log->user)
                            <p class="text-xs text-slate-400">{{ $log->user->email }}</p>
                        @endif
                    </td>
                    <td class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $log->occurrences }}</td>
                    <td class="text-xs text-slate-500 whitespace-nowrap">
                        <span title="{{ optional($log->last_occurred_at)->format('d M Y H:i:s') }}">
                            {{ optional($log->last_occurred_at)->diffForHumans() }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.error-logs.show', $log) }}" class="btn-outline btn-sm">Investigate</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-400">
                        <svg class="mx-auto h-10 w-10 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        No errors match the current filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $logs->links() }}</div>
    @endif
</div>

@endsection
