@extends('layouts.app')
@section('title', $corporate->company_name . ' — Corporate Portal — ' . $hotel->name)

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">

    {{-- Corporate header banner --}}
    <div class="mb-8 rounded-2xl bg-gradient-to-r from-navy to-navy-light/80 dark:from-slate-800 dark:to-slate-700 p-6 text-white">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-white/60 mb-1">Corporate Booking Portal</p>
                <h1 class="text-2xl font-bold">{{ $corporate->company_name }}</h1>
                <p class="mt-1 text-sm text-white/80">
                    Booking at <span class="font-semibold">{{ $hotel->name }}</span> · {{ $hotel->city }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-amber-300">{{ $corporate->discountLabel() }}</p>
                <p class="text-xs text-white/60 mt-1">Your negotiated rate</p>
                @if($corporate->contract_end)
                <p class="text-xs text-white/60">Contract until {{ $corporate->contract_end->format('d M Y') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Hotel info --}}
    <div class="mb-6 flex flex-wrap items-center gap-4">
        @if($hotel->featuredImage)
        <img src="{{ $hotel->featuredImage->url }}" alt="{{ $hotel->name }}"
            class="h-20 w-28 rounded-xl object-cover shrink-0">
        @endif
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $hotel->name }}</h2>
            @if($hotel->address)
            <p class="text-sm text-slate-500">{{ $hotel->address }}</p>
            @endif
            @if($hotel->phone || $hotel->email)
            <p class="text-xs text-slate-400 mt-1">
                @if($hotel->phone){{ $hotel->phone }}@endif
                @if($hotel->phone && $hotel->email) &nbsp;·&nbsp; @endif
                @if($hotel->email){{ $hotel->email }}@endif
            </p>
            @endif
        </div>
        <div class="ml-auto text-sm text-slate-500 dark:text-slate-400 text-right">
            <p>Check-in: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $hotel->check_in_time ?? '14:00' }}</span></p>
            <p>Check-out: <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $hotel->check_out_time ?? '11:00' }}</span></p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- Date picker + room list --}}
    <div x-data="corporatePortal()" x-init="init()">
        <div class="card p-5 mb-6">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">Select Your Dates</h3>
            <div class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="form-label">Check-in</label>
                    <input type="date" x-model="checkIn" :min="today"
                        @change="checkAvailability"
                        class="form-input">
                </div>
                <div>
                    <label class="form-label">Check-out</label>
                    <input type="date" x-model="checkOut" :min="minCheckOut"
                        @change="checkAvailability"
                        class="form-input">
                </div>
                <div>
                    <label class="form-label">Guests</label>
                    <select x-model="guests" @change="checkAvailability" class="form-input">
                        @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="text-sm text-slate-500 dark:text-slate-400" x-show="nights > 0">
                    <span x-text="nights"></span> night<span x-show="nights !== 1">s</span>
                </div>
            </div>
        </div>

        {{-- Available rooms --}}
        <div x-show="checkIn && checkOut" x-cloak>

            <div x-show="loading" class="text-center py-8 text-slate-400">Checking availability…</div>

            <div x-show="!loading" class="space-y-4">

                {{-- Pre-compute auth state once to avoid @auth/@else inside @foreach --}}
                <?php
                    $isLoggedIn = auth()->check();
                    $loginUrl   = route('login') . '?redirect=' . urlencode(request()->fullUrl());
                    $csrfField  = csrf_field();
                    $cartUrl    = route('booking.cart.store');
                ?>

                @if($roomTypes->isEmpty())
                <div class="card p-8 text-center text-slate-400">No room types configured for this hotel.</div>
                @endif

                @foreach($roomTypes as $rt)
                <?php
                    $rtImage       = $rt->images->isNotEmpty() ? $rt->images->first()->url : null;
                    $rtMeta        = $rt->bed_type . ' · ' . $rt->beds_count . ' bed' . ($rt->beds_count > 1 ? 's' : '');
                    if ($rt->size_sqm)  $rtMeta .= ' · ' . $rt->size_sqm . 'm²';
                    if ($rt->view_type) $rtMeta .= ' · ' . ucfirst($rt->view_type) . ' view';
                ?>
                <div class="card p-5">
                    <div class="flex flex-wrap gap-5 items-start">

                        @if($rtImage)
                        <img src="{{ $rtImage }}" alt="{{ $rt->name }}"
                            class="h-28 w-40 shrink-0 rounded-xl object-cover">
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white">{{ $rt->name }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $rtMeta }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xs text-slate-400 line-through">
                                        {{ money($rt->base_price) }}/night
                                    </p>
                                    <p class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ money($rt->corporate_price) }}<span class="text-xs font-normal text-slate-400">/night</span>
                                    </p>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold">
                                        {{ $corporate->discountLabel() }} applied
                                    </p>
                                </div>
                            </div>

                            @if($rt->description)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 line-clamp-2">{{ $rt->description }}</p>
                            @endif

                            {{-- Availability & booking (auth state pre-computed above) --}}
                            <div x-show="availabilityLoaded">
                                <template x-if="getRoom({{ $rt->id }})">
                                    <div>
                                        <p class="text-xs text-slate-500 mb-2"
                                            x-text="getRoom({{ $rt->id }}).available_count + ' room(s) available'"></p>
                                        @if($isLoggedIn)
                                        <form method="POST" action="{{ $cartUrl }}">
                                            {!! $csrfField !!}
                                            <input type="hidden" name="room_type_id" value="{{ $rt->id }}">
                                            <input type="hidden" name="check_in" :value="checkIn">
                                            <input type="hidden" name="check_out" :value="checkOut">
                                            <input type="hidden" name="guests" :value="guests">
                                            <input type="hidden" name="corporate_code" value="{{ $corporate->access_code }}">
                                            <button type="submit" class="btn-gold btn-sm">Reserve at Corporate Rate</button>
                                        </form>
                                        @else
                                        <a href="{{ $loginUrl }}" class="btn-outline btn-sm">Sign in to Book</a>
                                        @endif
                                    </div>
                                </template>
                                <template x-if="!getRoom({{ $rt->id }})">
                                    <p class="text-xs text-rose-500">Not available for selected dates</p>
                                </template>
                            </div>
                            <div x-show="!availabilityLoaded && checkIn && checkOut" class="text-xs text-slate-400 italic">
                                Select dates to check availability
                            </div>
                            <div x-show="!checkIn || !checkOut" class="text-xs text-slate-400 italic">
                                Select check-in and check-out dates
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Prompt to select dates --}}
        <div x-show="!checkIn || !checkOut" class="card p-10 text-center">
            <svg class="h-12 w-12 mx-auto text-slate-200 dark:text-slate-700 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-slate-500 dark:text-slate-400">Select check-in and check-out dates to see available rooms.</p>
        </div>
    </div>

    {{-- Footer note --}}
    <p class="mt-8 text-center text-xs text-slate-400">
        This is a private corporate booking portal for <strong>{{ $corporate->company_name }}</strong> employees.
        For support, contact {{ $hotel->email ?? 'the hotel' }}.
    </p>
</div>

@push('scripts')
<script>
function corporatePortal() {
    return {
        checkIn: '',
        checkOut: '',
        guests: 1,
        today: new Date().toISOString().split('T')[0],
        rooms: [],
        loading: false,
        availabilityLoaded: false,

        get minCheckOut() {
            if (!this.checkIn) return this.today;
            const d = new Date(this.checkIn);
            d.setDate(d.getDate() + 1);
            return d.toISOString().split('T')[0];
        },

        get nights() {
            if (!this.checkIn || !this.checkOut) return 0;
            const diff = new Date(this.checkOut) - new Date(this.checkIn);
            return Math.max(0, diff / 86400000);
        },

        init() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('check_in')) this.checkIn = params.get('check_in');
            if (params.get('check_out')) this.checkOut = params.get('check_out');
        },

        getRoom(id) {
            return this.rooms.find(r => r.id === id) || null;
        },

        async checkAvailability() {
            if (!this.checkIn || !this.checkOut) return;
            this.loading = true;
            this.availabilityLoaded = false;
            try {
                const res = await fetch(
                    `{{ route('hotels.availability', $hotel) }}?check_in=${this.checkIn}&check_out=${this.checkOut}&guests=${this.guests}`
                );
                const data = await res.json();
                this.rooms = data.room_types || [];
                this.availabilityLoaded = true;
            } catch (e) {
                this.rooms = [];
            }
            this.loading = false;
        }
    };
}
</script>
@endpush

@endsection
