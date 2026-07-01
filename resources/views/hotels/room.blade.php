@extends('layouts.app')
@section('title', "{$roomType->name} — {$hotel->name}")

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Breadcrumb --}}
    <nav class="mb-4 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('hotels.index') }}" class="hover:text-navy dark:hover:text-navy-light">{{ __('Hotels') }}</a>
        <span>/</span>
        <a href="{{ route('hotels.show', $hotel) }}" class="hover:text-navy dark:hover:text-navy-light truncate max-w-[160px]">{{ $hotel->name }}</a>
        <span>/</span>
        <span class="text-slate-900 dark:text-white">{{ $roomType->name }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-3">

        {{-- ── Left: room details ── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Images --}}
            @php
                $featuredIdx = $roomType->images->search(fn($img) => $img->is_featured);
                $initialIdx  = $featuredIdx !== false ? $featuredIdx : 0;
            @endphp
            <div x-data='{ "current": {{ $initialIdx }}, "images": @json($roomType->images->pluck("url")->values()) }'>
                @if($roomType->images->isNotEmpty())
                    <div class="overflow-hidden rounded-2xl bg-slate-100 dark:bg-slate-800 shadow-lg">
                        <img :src="images[current]" alt="{{ $roomType->name }}"
                             class="h-72 w-full object-cover sm:h-96 transition-opacity duration-300"
                             data-gallery-img
                             x-on:error="if($el.src !== '{{ asset('images/room-placeholder.svg') }}') $el.src='{{ asset('images/room-placeholder.svg') }}'">
                    </div>
                    @if($roomType->images->count() > 1)
                    <div class="mt-2 grid grid-cols-5 gap-2">
                        @foreach($roomType->images->take(5) as $idx => $img)
                        <button @click="current = {{ $idx }}"
                                :class="current === {{ $idx }} ? 'ring-2 ring-navy' : 'opacity-70 hover:opacity-100'"
                                class="overflow-hidden rounded-xl transition bg-slate-100 dark:bg-slate-800">
                            <img src="{{ $img->url }}" alt=""
                                 class="h-16 w-full object-cover"
                                 onerror="this.src='{{ asset('images/room-placeholder.svg') }}'; this.onerror=null;">
                        </button>
                        @endforeach
                    </div>
                    @endif
                @else
                    <div class="flex h-72 items-center justify-center rounded-2xl bg-slate-200 dark:bg-slate-700 sm:h-96">
                        <svg class="h-16 w-16 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Room header --}}
            <div class="card p-6" data-reveal>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $roomType->name }}</h1>
                        <div class="mt-2 flex flex-wrap gap-3 text-sm text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v9a1 1 0 001 1h4v-5h4v5h4a1 1 0 001-1v-9"/></svg>
                                {{ ucfirst($roomType->bed_type) }} {{ __('bed') }} × {{ $roomType->beds_count }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ __('Up to') }} {{ $roomType->max_guests }} {{ Str::plural(__('guest'), $roomType->max_guests) }}
                            </span>
                            @if($roomType->size_sqm)
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                                {{ $roomType->size_sqm }} m²
                            </span>
                            @endif
                            @if($roomType->view_type)
                            <span>{{ $roomType->view_type }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-navy dark:text-navy-light">
                            {{ config('app.currency') }} <span data-count="{{ $roomType->base_price }}"
                                      data-count-suffix=""
                                      class="tabular-nums">{{ number_format($roomType->base_price, 0) }}</span>
                        </div>
                        <div class="text-xs text-slate-500">{{ __('per night') }}</div>
                    </div>
                </div>

                @if($roomType->description)
                    <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $roomType->description }}</p>
                @endif
            </div>

            {{-- Room amenities --}}
            @if($roomType->amenities->isNotEmpty())
            <div class="card p-6" data-reveal>
                <h2 class="section-title mb-4">{{ __('Room Amenities') }}</h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3" data-stagger="40">
                    @foreach($roomType->amenities as $amenity)
                    <div class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                        <svg class="h-4 w-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $amenity->name }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Availability calendar --}}
            <div class="card p-6">
                <h2 class="section-title mb-1">{{ __('Availability') }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ __('Calendar shows availability for the current month.') }}</p>

                <div x-data="availabilityCalendar(
                    @json($calendar),
                    '{{ now()->year }}',
                    '{{ now()->month }}',
                    '{{ route('hotels.room.calendar', [$hotel, $roomType, '__YEAR__', '__MONTH__']) }}'
                )">
                    {{-- Nav --}}
                    <div class="flex items-center justify-between mb-3">
                        <button @click="prevMonth()" :disabled="isPrevDisabled()"
                                class="btn-ghost btn-sm disabled:opacity-40">← {{ __('Prev') }}</button>
                        <span class="text-sm font-semibold text-slate-900 dark:text-white" x-text="monthLabel()"></span>
                        <button @click="nextMonth()" class="btn-ghost btn-sm">{{ __('Next') }} →</button>
                    </div>

                    {{-- Day headers --}}
                    <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-400 mb-1">
                        <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']">
                            <div x-text="d"></div>
                        </template>
                    </div>

                    {{-- Day cells --}}
                    <div class="grid grid-cols-7 gap-1">
                        <template x-for="blank in leadingBlanks()" :key="'b' + blank">
                            <div></div>
                        </template>
                        <template x-for="day in days" :key="day.date">
                            <div :class="{
                                    'bg-rose-100 text-rose-400 dark:bg-rose-900/30 dark:text-rose-400 cursor-not-allowed': day.status === 'booked',
                                    'bg-slate-100 text-slate-300 dark:bg-slate-800 dark:text-slate-600 cursor-not-allowed': day.status === 'past',
                                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 cursor-default': day.status === 'available',
                                 }"
                                 class="flex h-9 items-center justify-center rounded-lg text-xs font-medium">
                                <span x-text="day.date.split('-')[2]"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Legend --}}
                    <div class="mt-3 flex gap-4 text-xs text-slate-500 dark:text-slate-400">
                        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded bg-emerald-100"></span> {{ __('Available') }}</span>
                        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded bg-rose-100"></span> {{ __('Booked') }}</span>
                    </div>
                </div>
            </div>

            {{-- Hotel info strip --}}
            <div class="card p-5 flex items-center gap-4">
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Part of') }}</p>
                    <a href="{{ route('hotels.show', $hotel) }}"
                       class="font-semibold text-navy dark:text-navy-light hover:underline">{{ $hotel->name }}</a>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $hotel->city }}, {{ $hotel->country }}</p>
                </div>
                <a href="{{ route('hotels.show', $hotel) }}" class="btn-ghost btn-sm shrink-0">{{ __('View Hotel') }} →</a>
            </div>
        </div>

        {{-- ── Sidebar: Book this room ── --}}
        <aside>
            <div class="card p-5 sticky top-20 space-y-4"
                 x-data="roomBooker(
                     {{ $roomType->id }},
                     '{{ route('hotels.availability', $hotel) }}',
                     '{{ route('booking.cart.store') }}'
                 )">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $roomType->name }}</h3>
                    <p class="text-2xl font-bold text-navy dark:text-navy-light mt-0.5">
                        {{ money($roomType->base_price) }}<span class="text-sm font-normal text-slate-500"> / {{ __('night') }}</span>
                    </p>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="form-label">{{ __('Check-in') }}</label>
                        <input type="date" x-model="checkIn" @change="clearResult()"
                               class="form-input" min="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Check-out') }}</label>
                        <input type="date" x-model="checkOut" @change="clearResult()"
                               class="form-input" :min="checkIn || '{{ now()->addDay()->toDateString() }}'">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Guests') }}</label>
                        <input type="number" x-model.number="guests" min="1"
                               max="{{ $roomType->max_guests }}" class="form-input">
                        <p class="mt-0.5 text-xs text-slate-400">{{ __('Max') }} {{ $roomType->max_guests }} {{ __('guests') }}</p>
                    </div>
                </div>

                {{-- Price summary --}}
                <div x-show="nights > 0" class="rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3 text-sm space-y-1">
                    <div class="flex justify-between text-slate-600 dark:text-slate-300">
                        <span x-text="'{{ money($roomType->base_price) }} × ' + nights + ' {{ __('nights') }}'"></span>
                        <span x-text="'{{ config('app.currency') }} ' + ({{ $roomType->base_price }} * nights).toLocaleString()"></span>
                    </div>
                    <div class="flex justify-between font-bold text-slate-900 dark:text-white pt-1 border-t border-slate-200 dark:border-slate-600">
                        <span>{{ __('Total') }}</span>
                        <span x-text="'{{ config('app.currency') }} ' + ({{ $roomType->base_price }} * nights).toLocaleString()"></span>
                    </div>
                </div>

                {{-- Availability result --}}
                <div x-show="result !== null">
                    <div x-show="result === false"
                         class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 p-3 text-sm text-rose-700 dark:text-rose-300">
                        {{ __('No rooms available for these dates.') }}
                    </div>
                </div>

                @auth
                <form method="POST" :action="cartUrl" @submit.prevent="addToCart($el)" x-show="result !== false">
                    @csrf
                    <input type="hidden" name="room_type_id" :value="roomTypeId">
                    <input type="hidden" name="check_in"     :value="checkIn">
                    <input type="hidden" name="check_out"    :value="checkOut">
                    <input type="hidden" name="guests"       :value="guests">
                    <button type="submit"
                            :disabled="!checkIn || !checkOut || loading"
                            class="btn-gold w-full">
                        <span x-show="!loading">{{ __('Reserve Now') }}</span>
                        <span x-show="loading">{{ __('Checking…') }}</span>
                    </button>
                </form>
                @else
                <a href="{{ route('login') }}" class="btn-outline w-full block text-center">{{ __('Sign in to Book') }}</a>
                @endauth

                @if($hotel->cancellation_policy)
                <p class="text-xs text-slate-400 dark:text-slate-500">
                    <span class="font-medium text-slate-600 dark:text-slate-300">{{ __('Cancellation') }}:</span>
                    {{ $hotel->cancellation_policy }}
                </p>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
function availabilityCalendar(initialDays, year, month, calendarUrlTemplate) {
    return {
        days: initialDays,
        year: parseInt(year),
        month: parseInt(month),

        monthLabel() {
            return new Date(this.year, this.month - 1, 1)
                .toLocaleString('default', { month: 'long', year: 'numeric' });
        },

        leadingBlanks() {
            const dow = new Date(this.year, this.month - 1, 1).getDay();
            return Array.from({ length: dow }, (_, i) => i);
        },

        isPrevDisabled() {
            const now = new Date();
            return this.year < now.getFullYear() ||
                (this.year === now.getFullYear() && this.month <= now.getMonth() + 1);
        },

        async prevMonth() {
            if (this.isPrevDisabled()) return;
            let m = this.month - 1, y = this.year;
            if (m < 1) { m = 12; y--; }
            await this.loadMonth(y, m);
        },

        async nextMonth() {
            let m = this.month + 1, y = this.year;
            if (m > 12) { m = 1; y++; }
            await this.loadMonth(y, m);
        },

        async loadMonth(y, m) {
            const url = calendarUrlTemplate
                .replace('__YEAR__', y)
                .replace('__MONTH__', m);
            try {
                const res  = await fetch(url);
                const data = await res.json();
                this.days  = data.calendar;
                this.year  = y;
                this.month = m;
            } catch (e) { console.error(e); }
        },
    };
}

function roomBooker(roomTypeId, availabilityUrl, cartUrl) {
    return {
        roomTypeId,
        availabilityUrl,
        cartUrl,
        checkIn:  '{{ request('check_in', '') }}',
        checkOut: '{{ request('check_out', '') }}',
        guests:   {{ request('guests', 1) }},
        loading:  false,
        result:   null,

        get nights() {
            if (!this.checkIn || !this.checkOut) return 0;
            const diff = (new Date(this.checkOut) - new Date(this.checkIn)) / 86400000;
            return diff > 0 ? diff : 0;
        },

        clearResult() { this.result = null; },

        async addToCart(form) {
            if (!this.checkIn || !this.checkOut) return;
            this.loading = true;
            this.result  = null;

            // Check availability first
            try {
                const url = new URL(this.availabilityUrl, location.origin);
                url.searchParams.set('check_in',  this.checkIn);
                url.searchParams.set('check_out', this.checkOut);
                url.searchParams.set('guests',    this.guests);
                const res  = await fetch(url);
                const data = await res.json();
                const room = (data.room_types || []).find(r => r.id === this.roomTypeId);
                if (!room || room.available_count < 1) {
                    this.result  = false;
                    this.loading = false;
                    return;
                }
            } catch (e) { console.error(e); }

            this.result = true;
            form.submit();
        },
    };
}
</script>
@endpush
