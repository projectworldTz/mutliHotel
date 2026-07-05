@extends('layouts.admin')
@section('title', 'Error ' . $errorLog->code)
@section('page-title', 'Error Details')

@section('content')

<div class="mb-5 flex items-center justify-between flex-wrap gap-3">
    <div>
        <p class="font-mono text-lg font-bold text-slate-900 dark:text-white">{{ $errorLog->code }}</p>
        <p class="text-sm text-slate-500">{{ $errorLog->exception_class }}</p>
    </div>
    @php $color = $errorLog->status_color; @endphp
    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold
        {{ $color === 'emerald' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' :
           ($color === 'rose'   ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' :
                                  'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300') }}">
        {{ ucfirst($errorLog->status) }}
    </span>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">

        {{-- Message --}}
        <div class="card p-5">
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">Message</h3>
            <p class="text-sm text-rose-600 dark:text-rose-400 font-mono break-words">{{ $errorLog->message ?: '—' }}</p>
            <p class="mt-3 text-xs text-slate-500 font-mono">{{ $errorLog->file }}:{{ $errorLog->line }}</p>
        </div>

        {{-- Stack trace --}}
        <div class="card p-5">
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">Stack Trace</h3>
            <pre class="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900 rounded-lg p-4 overflow-x-auto max-h-96 overflow-y-auto whitespace-pre-wrap">{{ $errorLog->trace }}</pre>
        </div>

        {{-- Request context --}}
        <div class="card p-5">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Request Context</h3>
            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">Method</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $errorLog->http_method ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs text-slate-500">URL</dt>
                    <dd class="font-medium text-slate-900 dark:text-white break-all">{{ $errorLog->url ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">IP Address</dt>
                    <dd class="font-medium text-slate-900 dark:text-white font-mono">{{ $errorLog->ip_address ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">User Agent</dt>
                    <dd class="text-xs text-slate-600 dark:text-slate-300 break-all">{{ $errorLog->user_agent ?? '—' }}</dd>
                </div>
            </dl>

            @if($errorLog->request_data)
            <div class="mt-4">
                <dt class="text-xs text-slate-500 mb-1">Input Data</dt>
                <pre class="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900 rounded-lg p-4 overflow-x-auto max-h-56 overflow-y-auto">{{ json_encode($errorLog->request_data, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
    </div>

    <div class="space-y-6">

        {{-- Who / where --}}
        <div class="card p-5">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Affected</h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-slate-500">Hotel</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">
                        @if($errorLog->hotel)
                            <a href="{{ route('admin.hotels.show', $errorLog->hotel) }}" class="text-navy dark:text-amber-400 hover:underline">{{ $errorLog->hotel->name }}</a>
                        @else
                            <span class="text-slate-400">Platform-level</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">User</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">
                        @if($errorLog->user)
                            {{ $errorLog->user->name }}
                            <p class="text-xs text-slate-400 font-normal">{{ $errorLog->user->email }}</p>
                        @else
                            <span class="text-slate-400">Guest / unauthenticated</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Occurrences</dt>
                    <dd class="font-medium text-slate-900 dark:text-white">{{ $errorLog->occurrences }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">First Seen</dt>
                    <dd class="text-slate-700 dark:text-slate-300">{{ $errorLog->created_at->format('d M Y H:i:s') }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-slate-500">Last Seen</dt>
                    <dd class="text-slate-700 dark:text-slate-300">{{ optional($errorLog->last_occurred_at)->format('d M Y H:i:s') }}</dd>
                </div>
                @if($errorLog->resolved_at)
                <div>
                    <dt class="text-xs text-slate-500">Resolved</dt>
                    <dd class="text-slate-700 dark:text-slate-300">
                        {{ $errorLog->resolved_at->format('d M Y H:i:s') }}
                        @if($errorLog->resolvedBy) by {{ $errorLog->resolvedBy->name }} @endif
                    </dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Resolve / investigate --}}
        <div class="card p-5">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Resolve</h3>
            <form method="POST" action="{{ route('admin.error-logs.update', $errorLog) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                    <select name="status" class="form-input w-full text-sm">
                        <option value="open"     @selected($errorLog->status === 'open')>Open</option>
                        <option value="resolved" @selected($errorLog->status === 'resolved')>Resolved</option>
                        <option value="ignored"  @selected($errorLog->status === 'ignored')>Ignored (not a bug)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Resolution Notes</label>
                    <textarea name="resolution_notes" rows="4" class="form-input w-full text-sm"
                              placeholder="What was wrong and how it was fixed (e.g. corrected hotel settings, re-synced data)…">{{ old('resolution_notes', $errorLog->resolution_notes) }}</textarea>
                </div>
                <button type="submit" class="btn-primary btn-sm w-full">Save</button>
            </form>
        </div>

        @if($errorLog->status !== 'open')
        <form method="POST" action="{{ route('admin.error-logs.destroy', $errorLog) }}"
              onsubmit="return confirm('Delete error {{ $errorLog->code }}? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-outline btn-sm w-full text-rose-600 border-rose-200 hover:bg-rose-50">Delete Record</button>
        </form>
        @endif

        <a href="{{ route('admin.error-logs.index') }}" class="btn-ghost btn-sm w-full block text-center">&larr; Back to all errors</a>
    </div>
</div>

@endsection
