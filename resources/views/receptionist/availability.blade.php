@extends('layouts.receptionist')
@section('title', __('Availability'))
@section('page-title', __('Room Availability'))

@section('content')
<div class="mb-5 flex flex-wrap items-center gap-4">
    <p class="text-sm text-slate-500 dark:text-slate-400">
        {{ __('Current month availability per room type.') }}
        <span class="inline-flex items-center gap-1 ml-2"><span class="inline-block h-3 w-3 rounded bg-emerald-100"></span> {{ __('Available') }}</span>
        <span class="inline-flex items-center gap-1 ml-1"><span class="inline-block h-3 w-3 rounded bg-amber-100"></span> {{ __('Partially Booked') }}</span>
        <span class="inline-flex items-center gap-1 ml-1"><span class="inline-block h-3 w-3 rounded bg-rose-100"></span> {{ __('Fully Booked') }}</span>
    </p>
    <a href="{{ route('receptionist.bookings.create') }}" class="btn-gold btn-sm ml-auto">+ {{ __('New Booking') }}</a>
</div>

@forelse($grid as $item)
@php $rt = $item['room_type']; $calendar = $item['calendar']; @endphp
<div class="card mb-5" x-data='availabilityCalendar(
    @json($calendar),
    {{ now()->year }},
    {{ now()->month }},
    "{{ route('receptionist.availability.calendar', [$rt, '__YEAR__', '__MONTH__']) }}"
)'>
    <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
        <div>
            <h3 class="font-bold text-slate-900 dark:text-white">{{ $rt->name }}</h3>
            <p class="text-xs text-slate-500">{{ $rt->beds_count }}× {{ $rt->bed_type }} · {{ __('Max') }} {{ $rt->max_guests }} {{ __('guests') }} · {{ $item['total'] }} {{ __('rooms') }}</p>
        </div>
        <p class="text-lg font-bold text-navy dark:text-navy-light">{{ money($rt->base_price) }}<span class="text-xs font-normal text-slate-400">/{{ __('night') }}</span></p>
    </div>

    <div class="p-5">
        {{-- Nav --}}
        <div class="flex items-center justify-between mb-3">
            <button @click="prevMonth()" :disabled="isPrevDisabled()" class="btn-ghost btn-sm disabled:opacity-40">← {{ __('Prev') }}</button>
            <span class="text-sm font-semibold text-slate-900 dark:text-white" x-text="monthLabel()"></span>
            <button @click="nextMonth()" class="btn-ghost btn-sm">{{ __('Next') }} →</button>
        </div>
        {{-- Day labels --}}
        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-400 mb-1">
            <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']"><div x-text="d"></div></template>
        </div>
        {{-- Cells --}}
        <div class="grid grid-cols-7 gap-1">
            <template x-for="blank in leadingBlanks()" :key="'b'+blank"><div></div></template>
            <template x-for="day in days" :key="day.date">
                <div :class="{
                        'bg-rose-100 text-rose-400 dark:bg-rose-900/30': day.status === 'booked',
                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30': day.status === 'partial',
                        'bg-slate-100 text-slate-300 dark:bg-slate-800': day.status === 'past',
                        'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20': day.status === 'available',
                     }"
                     class="flex h-9 items-center justify-center rounded-lg text-xs font-medium">
                    <span x-text="day.date.split('-')[2]"></span>
                </div>
            </template>
        </div>
    </div>
</div>
@empty
<p class="text-slate-500">{{ __('No room types configured for this hotel.') }}</p>
@endforelse

@push('scripts')
<script>
function availabilityCalendar(initialDays, year, month, urlTemplate) {
    return {
        days: initialDays, year: parseInt(year), month: parseInt(month),
        monthLabel() { return new Date(this.year, this.month - 1, 1).toLocaleString('default', { month: 'long', year: 'numeric' }); },
        leadingBlanks() { const dow = new Date(this.year, this.month - 1, 1).getDay(); return Array.from({ length: dow }, (_, i) => i); },
        isPrevDisabled() { const n = new Date(); return this.year < n.getFullYear() || (this.year === n.getFullYear() && this.month <= n.getMonth() + 1); },
        async prevMonth() { if (this.isPrevDisabled()) return; let m = this.month - 1, y = this.year; if (m < 1) { m = 12; y--; } await this.load(y, m); },
        async nextMonth() { let m = this.month + 1, y = this.year; if (m > 12) { m = 1; y++; } await this.load(y, m); },
        async load(y, m) {
            const url = urlTemplate.replace('__YEAR__', y).replace('__MONTH__', m);
            const data = await fetch(url).then(r => r.json());
            this.days = data.calendar; this.year = y; this.month = m;
        },
    };
}
</script>
@endpush
@endsection
