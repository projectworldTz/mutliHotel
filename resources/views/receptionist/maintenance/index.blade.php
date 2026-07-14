@extends('layouts.receptionist')
@section('title', __('Maintenance'))
@section('page-title', __('Maintenance Requests'))

@section('content')

<div class="grid gap-4 sm:grid-cols-3 mb-6">
    @foreach([
        [__('Pending'),     $summary['pending'],     'text-amber-600 dark:text-amber-400',   'bg-amber-50 dark:bg-amber-900/20'],
        [__('In Progress'), $summary['in_progress'], 'text-blue-600 dark:text-blue-400',     'bg-blue-50 dark:bg-blue-900/20'],
        [__('Resolved'),    $summary['resolved'],    'text-emerald-600 dark:text-emerald-400','bg-emerald-50 dark:bg-emerald-900/20'],
    ] as [$label, $count, $textColor, $bgColor])
    <div class="stat-card">
        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
        <p class="mt-2 text-3xl font-bold {{ $textColor }}">{{ $count }}</p>
    </div>
    @endforeach
</div>

<div class="flex flex-wrap gap-3 items-center justify-between mb-5">
    <form method="GET" class="flex flex-wrap gap-2">
        <select name="status" class="form-input w-auto text-sm" onchange="this.form.submit()">
            <option value="">{{ __('All Statuses') }}</option>
            @foreach(['pending' => __('Pending'), 'in_progress' => __('In Progress'), 'resolved' => __('Resolved')] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('status') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        <select name="priority" class="form-input w-auto text-sm" onchange="this.form.submit()">
            <option value="">{{ __('All Priorities') }}</option>
            @foreach(['urgent' => __('Urgent'), 'high' => __('High'), 'normal' => __('Normal')] as $val => $lbl)
            <option value="{{ $val }}" @selected(request('priority') === $val)>{{ $lbl }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['status', 'priority']))
        <a href="{{ route('receptionist.maintenance.index') }}" class="btn-ghost btn-sm">{{ __('Clear') }}</a>
        @endif
    </form>

    <button x-data @click="$dispatch('open-create-maintenance')" class="btn-primary btn-sm flex items-center gap-2">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('Log Issue') }}
    </button>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Room') }}</th>
                    <th>{{ __('Category') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Priority') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Reported By') }}</th>
                    <th class="w-40">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                @php
                    $statusColors = ['pending'=>'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400','in_progress'=>'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400','resolved'=>'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'];
                    $priorityColors = ['urgent'=>'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400','high'=>'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400','normal'=>'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300'];
                @endphp
                <tr class="tr-hover {{ $req->priority === 'urgent' ? 'border-l-2 border-rose-400' : '' }}">
                    <td class="font-mono text-sm">{{ $req->room->room_number ?? '—' }}</td>
                    <td class="text-sm capitalize">{{ $req->category }}</td>
                    <td class="text-sm text-slate-600 dark:text-slate-300 max-w-[220px] truncate" title="{{ $req->description }}">{{ $req->description }}</td>
                    <td><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $priorityColors[$req->priority] }}">{{ ucfirst($req->priority) }}</span></td>
                    <td><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$req->status] }}">{{ ucwords(str_replace('_',' ',$req->status)) }}</span></td>
                    <td class="text-sm text-slate-500">{{ $req->reporter->name ?? __('Guest') }}</td>
                    <td>
                        <div class="flex flex-wrap gap-1">
                            @if($req->status === 'pending')
                            <form method="POST" action="{{ route('receptionist.maintenance.status', $req) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="start">
                                <button class="rounded-lg bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold px-2.5 py-1.5 transition">{{ __('Start') }}</button>
                            </form>
                            <form method="POST" action="{{ route('receptionist.maintenance.destroy', $req) }}" onsubmit="return confirm('{{ __('Remove this request?') }}')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-slate-300 dark:border-slate-600 text-slate-500 text-xs font-semibold px-2.5 py-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 transition">✕</button>
                            </form>
                            @elseif($req->status === 'in_progress')
                            <form method="POST" action="{{ route('receptionist.maintenance.status', $req) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="resolve">
                                <button class="rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold px-2.5 py-1.5 transition">{{ __('Resolve') }}</button>
                            </form>
                            @else
                            <span class="text-xs text-slate-400 italic">{{ __('Resolved') }}</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-14 text-center text-slate-500">{{ __('No maintenance requests.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $requests->links() }}</div>
    @endif
</div>

{{-- ── Log Issue Modal ──────────────────────────────────────────────────────── --}}
<div x-data="{ open: false }" x-on:open-create-maintenance.window="open = true" x-show="open" x-trap="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10" @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Log Maintenance Issue') }}</h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('receptionist.maintenance.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Room') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                <select name="room_id" class="form-input w-full">
                    <option value="">{{ __('No specific room') }}</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}">{{ __('Room') }} {{ $room->room_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Category') }}</label>
                <select name="category" class="form-input w-full" required>
                    <option value="plumbing">{{ __('Plumbing') }}</option>
                    <option value="electrical">{{ __('Electrical') }}</option>
                    <option value="hvac">{{ __('HVAC') }}</option>
                    <option value="furniture">{{ __('Furniture') }}</option>
                    <option value="appliance">{{ __('Appliance') }}</option>
                    <option value="other">{{ __('Other') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Priority') }}</label>
                <select name="priority" class="form-input w-full" required>
                    <option value="normal">{{ __('Normal') }}</option>
                    <option value="high">{{ __('High') }}</option>
                    <option value="urgent">{{ __('Urgent') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Description') }}</label>
                <textarea name="description" rows="3" class="form-input w-full resize-none" required placeholder="{{ __('What\'s wrong?') }}"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 btn-primary">{{ __('Log Issue') }}</button>
                <button type="button" @click="open = false" class="flex-1 btn-ghost">{{ __('Cancel') }}</button>
            </div>
        </form>
    </div>
</div>

@endsection
