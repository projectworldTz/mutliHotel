@extends('layouts.owner')
@section('title', 'Maintenance — ' . $hotel->name)
@section('page-title', 'Maintenance Requests')

@section('content')

<div class="mb-5">
    <a href="{{ route('owner.hotels.show', $hotel) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">← {{ $hotel->name }}</a>
</div>

<div class="grid gap-4 sm:grid-cols-3 mb-6">
    @foreach([
        ['Pending', $summary['pending'], 'text-amber-600 dark:text-amber-400', 'bg-amber-50 dark:bg-amber-900/20'],
        ['In Progress', $summary['in_progress'], 'text-blue-600 dark:text-blue-400', 'bg-blue-50 dark:bg-blue-900/20'],
        ['Resolved', $summary['resolved'], 'text-emerald-600 dark:text-emerald-400', 'bg-emerald-50 dark:bg-emerald-900/20'],
    ] as [$label, $count, $textColor, $bgColor])
    <div class="rounded-2xl {{ $bgColor }} border border-white/60 dark:border-slate-700 p-4">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ $label }}</p>
        <p class="text-2xl font-bold {{ $textColor }}">{{ $count }}</p>
    </div>
    @endforeach
</div>

<form method="GET" class="mb-4 flex gap-2">
    <select name="status" class="form-input w-auto text-sm" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        @foreach(['pending'=>'Pending','in_progress'=>'In Progress','resolved'=>'Resolved'] as $v => $l)
        <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
        @endforeach
    </select>
</form>

<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Room</th><th>Category</th><th>Description</th><th>Priority</th><th>Status</th><th>Reported</th></tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr class="tr-hover">
                    <td class="font-mono text-sm">{{ $req->room->room_number ?? '—' }}</td>
                    <td class="text-sm capitalize">{{ $req->category }}</td>
                    <td class="text-sm text-slate-600 dark:text-slate-300 max-w-[220px] truncate">{{ $req->description }}</td>
                    <td class="text-sm capitalize">{{ $req->priority }}</td>
                    <td class="text-sm">{{ ucwords(str_replace('_',' ',$req->status)) }}</td>
                    <td class="text-sm text-slate-500">{{ $req->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-14 text-center text-slate-500">No maintenance requests yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $requests->links() }}</div>
    @endif
</div>

@endsection
