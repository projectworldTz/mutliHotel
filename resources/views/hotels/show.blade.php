@extends('layouts.app')
@section('title', $hotel->name)

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    {{-- Breadcrumb --}}
    <nav class="mb-4 flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('hotels.index') }}" class="hover:text-navy dark:hover:text-navy-light">Hotels</a>
        <span>/</span>
        <span class="text-slate-900 dark:text-white truncate">{{ $hotel->name }}</span>
    </nav>

    <div class="grid gap-8 lg:grid-cols-3">
        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Image gallery --}}
            <div x-data="gallery()" class="relative">
                @if($hotel->images->isNotEmpty())
                    <div class="overflow-hidden rounded-2xl">
                        <img :src="images[current]" :alt="'Hotel image ' + (current + 1)"
                             class="h-72 w-full object-cover sm:h-96">
                    </div>
                    @if($hotel->images->count() > 1)
                    <div class="mt-2 grid grid-cols-5 gap-2">
                        @foreach($hotel->images->take(5) as $idx => $img)
                        <button @click="current = {{ $idx }}"
                                :class="current === {{ $idx }} ? 'ring-2 ring-navy' : 'opacity-70 hover:opacity-100'"
                                class="overflow-hidden rounded-xl transition">
                            <img src="{{ $img->url }}" alt="" class="h-16 w-full object-cover">
                        </button>
                        @endforeach
                    </div>
                    @endif
                @else
                    <div class="flex h-72 items-center justify-center rounded-2xl bg-slate-200 dark:bg-slate-700 sm:h-96">
                        <svg class="h-16 w-16 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Hotel header --}}
            <div class="card p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            @for($i = 1; $i <= 5; $i++)
                            <svg class="h-4 w-4 {{ $i <= $hotel->star_rating ? 'text-gold' : 'text-slate-300' }}"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            @endfor
                            @if($hotel->category)
                                <span class="badge bg-navy/10 text-navy dark:bg-navy/30 dark:text-navy-light">{{ $hotel->category->name }}</span>
                            @endif
                        </div>
                        <h1 class="text-3xl font-bold text-slate-900 dark:text-white">{{ $hotel->name }}</h1>
                        <p class="mt-2 flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $hotel->address }}, {{ $hotel->city }}, {{ $hotel->country }}
                        </p>
                    </div>
                    <div class="text-right">
                        @if($hotel->average_rating)
                            <div class="text-3xl font-bold text-navy dark:text-navy-light">{{ number_format($hotel->average_rating, 1) }}</div>
                            <div class="text-xs text-slate-500">out of 5</div>
                        @endif
                        @auth
                        <form method="POST" action="{{ route('favorites.toggle', $hotel) }}" class="mt-2">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-1.5 text-sm font-medium {{ auth()->user()->hasFavorited($hotel->id) ? 'text-rose-500' : 'text-slate-400 hover:text-rose-500' }} transition">
                                <svg class="h-4 w-4" fill="{{ auth()->user()->hasFavorited($hotel->id) ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                {{ auth()->user()->hasFavorited($hotel->id) ? 'Saved' : 'Save' }}
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>

                @if($hotel->description)
                    <p class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $hotel->description }}</p>
                @endif

                {{-- Info grid --}}
                <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="text-center rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Check-in</p>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $hotel->check_in_time ?? '14:00' }}</p>
                    </div>
                    <div class="text-center rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Check-out</p>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $hotel->check_out_time ?? '11:00' }}</p>
                    </div>
                    @if($hotel->phone)
                    <div class="text-center rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Phone</p>
                        <p class="font-bold text-slate-900 dark:text-white text-sm">{{ $hotel->phone }}</p>
                    </div>
                    @endif
                    @if($hotel->email)
                    <div class="text-center rounded-xl bg-slate-50 dark:bg-slate-700/50 p-3">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Email</p>
                        <p class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $hotel->email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Amenities --}}
            @if($hotel->amenities->isNotEmpty())
            <div class="card p-6">
                <h2 class="section-title mb-4">Amenities</h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach($hotel->amenities as $amenity)
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

            {{-- Room types --}}
            @if($hotel->roomTypes->isNotEmpty())
            <div class="card p-6">
                <h2 class="section-title mb-4">Room Types</h2>
                <div class="space-y-4">
                    @foreach($hotel->roomTypes as $rt)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 hover:border-navy/30 dark:hover:border-navy-light/30 transition">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-slate-900 dark:text-white">{{ $rt->name }}</h3>
                                <div class="mt-1 flex flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                                    <span>{{ $rt->bed_type }}</span>
                                    <span>·</span>
                                    <span>{{ $rt->beds_count }} {{ Str::plural('bed', $rt->beds_count) }}</span>
                                    <span>·</span>
                                    <span>Up to {{ $rt->max_guests }} {{ Str::plural('guest', $rt->max_guests) }}</span>
                                    @if($rt->size_sqm)<span>· {{ $rt->size_sqm }} m²</span>@endif
                                </div>
                                @if($rt->amenities->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($rt->amenities->take(4) as $a)
                                            <span class="rounded-full bg-slate-100 dark:bg-slate-700 px-2 py-0.5 text-xs text-slate-600 dark:text-slate-300">
                                                {{ $a->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold text-navy dark:text-navy-light">
                                    ${{ number_format($rt->base_price, 0) }}
                                </div>
                                <div class="text-xs text-slate-500">per night</div>
                                <a href="{{ route('hotels.room.show', [$hotel, $rt]) }}"
                                   class="btn-primary btn-sm mt-2">View Room</a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Reviews --}}
            @if($hotel->approvedReviews->isNotEmpty())
            <div class="card p-6">
                <h2 class="section-title mb-4">
                    Guest Reviews
                    <span class="ml-2 text-base font-normal text-slate-500">({{ $hotel->approvedReviews->count() }})</span>
                </h2>
                <div class="space-y-4">
                    @foreach($hotel->approvedReviews->take(5) as $review)
                    <div class="border-b border-slate-100 dark:border-slate-700 pb-4 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-navy/10 dark:bg-navy/30 text-sm font-bold text-navy dark:text-navy-light">
                                    {{ strtoupper(substr($review->user->name ?? 'G', 0, 1)) }}
                                </div>
                                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $review->user->name ?? 'Guest' }}</span>
                            </div>
                            <div class="flex text-gold text-sm">
                                @for($i = 1; $i <= $review->rating; $i++) ★ @endfor
                            </div>
                        </div>
                        @if($review->title)
                            <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $review->title }}</p>
                        @endif
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ Str::limit($review->comment, 200) }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- ── Sidebar: Availability checker ── --}}
        <aside class="space-y-4">
            <div class="card p-5 sticky top-20" x-data="availabilityChecker('{{ route('hotels.availability', $hotel) }}')">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-4">Check Availability</h3>

                <div class="space-y-3">
                    <div>
                        <label class="form-label">Check-in</label>
                        <input type="date" x-model="checkIn" class="form-input"
                               min="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label class="form-label">Check-out</label>
                        <input type="date" x-model="checkOut" class="form-input"
                               :min="checkIn || '{{ now()->addDay()->toDateString() }}'">
                    </div>
                    <div>
                        <label class="form-label">Guests</label>
                        <input type="number" x-model="guests" min="1" max="20" class="form-input">
                    </div>
                    <button @click="check()" :disabled="loading || !checkIn || !checkOut"
                            class="btn-primary w-full">
                        <span x-show="!loading">Check Availability</span>
                        <span x-show="loading">Checking…</span>
                    </button>
                </div>

                {{-- Results --}}
                <div x-show="checked" class="mt-4 space-y-3">
                    <template x-if="rooms.length === 0">
                        <div class="rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 p-3 text-sm text-rose-700 dark:text-rose-300">
                            No rooms available for these dates.
                        </div>
                    </template>
                    <template x-for="room in rooms" :key="room.id">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white" x-text="room.name"></p>
                                    <p class="text-xs text-slate-500" x-text="room.available_count + ' rooms left'"></p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-navy dark:text-navy-light" x-text="'$' + room.nightly_rate + '/night'"></p>
                                </div>
                            </div>
                            @auth
                            <form method="POST" action="{{ route('booking.cart.store') }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="room_type_id" :value="room.id">
                                <input type="hidden" name="check_in" :value="checkIn">
                                <input type="hidden" name="check_out" :value="checkOut">
                                <input type="hidden" name="guests" :value="guests">
                                <button type="submit" class="btn-gold btn-sm w-full">Reserve Now</button>
                            </form>
                            @else
                            <a href="{{ route('login') }}" class="btn-outline btn-sm w-full mt-2 block text-center">Sign in to Book</a>
                            @endauth
                        </div>
                    </template>
                </div>
            </div>

            @if($related->isNotEmpty())
            <div class="card p-4">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">Similar Hotels</h3>
                <div class="space-y-3">
                    @foreach($related as $r)
                    <a href="{{ route('hotels.show', $r) }}" class="flex items-center gap-3 group">
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-slate-200 dark:bg-slate-700">
                            @if($r->featuredImage)
                                <img src="{{ $r->featuredImage->url }}" alt="{{ $r->name }}" class="h-full w-full object-cover group-hover:scale-105 transition">
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold truncate text-slate-900 dark:text-white group-hover:text-navy dark:group-hover:text-navy-light transition">{{ $r->name }}</p>
                            <p class="text-xs text-slate-500">{{ $r->city }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
function gallery() {
    return {
        images: @json($hotel->images->pluck('url')->values()),
        current: 0,
    };
}

function availabilityChecker(apiUrl) {
    return {
        checkIn: '{{ request('check_in', now()->addDay()->toDateString()) }}',
        checkOut: '{{ request('check_out', now()->addDays(2)->toDateString()) }}',
        guests: {{ request('guests', 2) }},
        loading: false,
        checked: false,
        rooms: [],

        async check() {
            if (!this.checkIn || !this.checkOut) return;
            this.loading = true;
            this.checked = false;
            try {
                const url = new URL(apiUrl);
                url.searchParams.set('check_in', this.checkIn);
                url.searchParams.set('check_out', this.checkOut);
                url.searchParams.set('guests', this.guests);
                const res = await fetch(url);
                const data = await res.json();
                this.rooms = data.room_types || [];
                this.checked = true;
            } catch (e) {
                console.error(e);
            }
            this.loading = false;
        }
    };
}
</script>
@endpush
