@extends('layouts.owner')
@section('title', 'Staff Scheduling — ' . $hotel->name)
@section('page-title', 'Staff Scheduling')

@section('content')

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('owner.hotels.show', $hotel) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">← {{ $hotel->name }}</a>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Staff Scheduling</h2>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('owner.shifts.index', ['hotel' => $hotel, 'week' => $weekStart->copy()->subWeek()->toDateString()]) }}" class="btn-ghost btn-sm">← Prev</a>
        <span class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $weekStart->format('d M') }} – {{ $weekEnd->format('d M Y') }}</span>
        <a href="{{ route('owner.shifts.index', ['hotel' => $hotel, 'week' => $weekStart->copy()->addWeek()->toDateString()]) }}" class="btn-ghost btn-sm">Next →</a>
        <button x-data @click="$dispatch('open-add-shift')" class="btn-primary btn-sm ml-2">+ Add Shift</button>
    </div>
</div>

<div class="grid gap-3 lg:grid-cols-7">
    @for($d = $weekStart->copy(); $d->lte($weekEnd); $d->addDay())
    @php $dayShifts = $shifts->get($d->toDateString(), collect()); @endphp
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 p-3 min-h-[160px]">
        <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2">{{ $d->format('D, d M') }}</p>
        <div class="space-y-2">
            @forelse($dayShifts as $shift)
            <div class="rounded-lg bg-slate-50 dark:bg-slate-700/50 p-2 text-xs">
                <p class="font-semibold text-slate-900 dark:text-white">{{ $shift->user->name }}</p>
                <p class="text-slate-500 dark:text-slate-400">{{ $shift->time_range }}</p>
                @if($shift->role)<p class="text-slate-400">{{ $shift->role }}</p>@endif
                <form method="POST" action="{{ route('owner.shifts.destroy', [$hotel, $shift]) }}" onsubmit="return confirm('Remove this shift?')" class="mt-1">
                    @csrf @method('DELETE')
                    <button class="text-rose-500 hover:text-rose-600 text-[11px]">Remove</button>
                </form>
            </div>
            @empty
            <p class="text-xs text-slate-300 dark:text-slate-600 italic">No shifts</p>
            @endforelse
        </div>
    </div>
    @endfor
</div>

{{-- ── Add Shift Modal ──────────────────────────────────────────────────────── --}}
<div x-data="{ open: false }" x-on:open-add-shift.window="open = true" x-show="open" x-trap="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10" @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add Shift</h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('owner.shifts.store', $hotel) }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Staff Member</label>
                <select name="user_id" class="form-input w-full" required>
                    <option value="">Select…</option>
                    @foreach($staff as $s)
                    <option value="{{ $s->user_id }}">{{ $s->user->name }} ({{ ucfirst($s->position) }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Date</label>
                <input type="date" name="shift_date" class="form-input w-full" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Start</label>
                    <input type="time" name="start_time" class="form-input w-full" required>
                </div>
                <div>
                    <label class="form-label">End</label>
                    <input type="time" name="end_time" class="form-input w-full" required>
                </div>
            </div>
            <div>
                <label class="form-label">Role Label <span class="font-normal text-slate-400">(optional)</span></label>
                <input type="text" name="role" class="form-input w-full" placeholder="Front Desk, Night Audit…">
            </div>
            <div class="flex gap-3 pt-1">
                <button type="submit" class="flex-1 btn-primary">Add Shift</button>
                <button type="button" @click="open = false" class="flex-1 btn-ghost">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection
