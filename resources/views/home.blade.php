@extends('layouts.app')
@section('title', 'Find Your Perfect Stay')

@section('content')
{{-- ── Hero ── --}}
<section class="relative bg-gradient-to-br from-navy-dark via-navy to-navy-light overflow-hidden">
    <div class="absolute inset-0 opacity-10"
         style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 32px 32px;"></div>
    <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-white sm:text-5xl lg:text-6xl">
                Find Your Perfect Stay
            </h1>
            <p class="mt-4 text-lg text-slate-300 max-w-2xl mx-auto">
                Discover {{ number_format($stats['hotels']['total'] ?? 0) }}+ hotels worldwide. Book with confidence, cancel for free.
            </p>
        </div>

        {{-- Search form --}}
        <div class="mt-10 hero-glass mx-auto max-w-3xl" x-data="{ checkIn: '', checkOut: '' }">
            <form method="GET" action="{{ route('hotels.index') }}">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="lg:col-span-2">
                        <label class="block mb-1 text-xs font-medium text-white/80">Destination</label>
                        <input type="text" name="search"
                               class="w-full rounded-lg border border-white/30 bg-white/20 px-4 py-2.5 text-sm text-white placeholder:text-white/50 focus:border-white/60 focus:outline-none focus:ring-2 focus:ring-white/20"
                               placeholder="City, hotel name…"
                               value="{{ request('search') }}">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-white/80">Check-in</label>
                        <input type="date" name="check_in" x-model="checkIn"
                               class="w-full rounded-lg border border-white/30 bg-white/20 px-4 py-2.5 text-sm text-white focus:border-white/60 focus:outline-none focus:ring-2 focus:ring-white/20"
                               min="{{ now()->toDateString() }}">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-white/80">Check-out</label>
                        <input type="date" name="check_out"
                               class="w-full rounded-lg border border-white/30 bg-white/20 px-4 py-2.5 text-sm text-white focus:border-white/60 focus:outline-none focus:ring-2 focus:ring-white/20"
                               :min="checkIn || '{{ now()->addDay()->toDateString() }}'">
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-white/80">Guests</label>
                        <input type="number" name="guests" min="1" max="20" value="2"
                               class="w-20 rounded-lg border border-white/30 bg-white/20 px-3 py-2.5 text-sm text-white focus:border-white/60 focus:outline-none">
                    </div>
                    <button type="submit" class="btn-gold btn-lg">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search Hotels
                    </button>
                </div>
            </form>
        </div>

        {{-- Stats bar --}}
        <div class="mt-10 flex flex-wrap items-center justify-center gap-8 sm:gap-12 text-center">
            <div><div class="text-2xl font-bold text-gold">{{ number_format($stats['hotels']['active'] ?? 0) }}+</div><div class="text-sm text-slate-300">Active Hotels</div></div>
            <div><div class="text-2xl font-bold text-gold">{{ number_format($stats['bookings']['total'] ?? 0) }}+</div><div class="text-sm text-slate-300">Bookings Made</div></div>
            <div><div class="text-2xl font-bold text-gold">4.8/5</div><div class="text-sm text-slate-300">Average Rating</div></div>
        </div>
    </div>
</section>

{{-- ── Categories ── --}}
@if($categories->isNotEmpty())
<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white text-center mb-2">Browse by Type</h2>
    <p class="text-center text-slate-500 dark:text-slate-400 mb-8">Find exactly the experience you're looking for</p>
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        @foreach($categories->take(6) as $cat)
        <a href="{{ route('hotels.index', ['category_id' => $cat->id]) }}"
           class="card group flex flex-col items-center p-5 text-center hover:shadow-lg transition hover:-translate-y-0.5">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-navy/10 dark:bg-navy/30 group-hover:bg-navy/20 transition">
                <svg class="h-6 w-6 text-navy dark:text-navy-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $cat->name }}</p>
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- ── Featured Hotels ── --}}
@if($featured->isNotEmpty())
<section class="bg-white dark:bg-slate-900 py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Featured Hotels</h2>
                <p class="mt-1 text-slate-500 dark:text-slate-400">Hand-picked for exceptional experiences</p>
            </div>
            <a href="{{ route('hotels.index') }}" class="btn-outline btn-sm">View All →</a>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach($featured as $hotel)
            <a href="{{ route('hotels.show', $hotel) }}"
               class="card group hover:shadow-xl transition hover:-translate-y-1">
                <div class="relative h-48 overflow-hidden bg-slate-200 dark:bg-slate-700">
                    @if($hotel->featuredImage)
                        <img src="{{ $hotel->featuredImage->url }}" alt="{{ $hotel->name }}"
                             class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div class="flex h-full items-center justify-center">
                            <svg class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    @endif
                    <div class="absolute top-3 left-3 flex items-center gap-0.5 rounded-full bg-white/90 px-2 py-1 shadow">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="h-3 w-3 {{ $i <= $hotel->star_rating ? 'text-gold' : 'text-slate-300' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-navy dark:group-hover:text-navy-light transition line-clamp-1">
                        {{ $hotel->name }}
                    </h3>
                    <p class="mt-1 flex items-center gap-1 text-sm text-slate-500 dark:text-slate-400">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $hotel->city }}, {{ $hotel->country }}
                    </p>
                    <div class="mt-4 flex items-center justify-between">
                        @if($hotel->average_rating)
                            <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 px-2 py-0.5 rounded-full">
                                ★ {{ number_format($hotel->average_rating, 1) }}
                            </span>
                        @else
                            <span></span>
                        @endif
                        <span class="text-sm font-bold text-navy dark:text-navy-light">
                            @if($hotel->roomTypes->isNotEmpty())
                                From ${{ number_format($hotel->roomTypes->min('base_price'), 0) }}<span class="font-normal text-slate-400">/night</span>
                            @endif
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ── Why Choose Us ── --}}
<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white text-center mb-2">Why Book With Us</h2>
    <p class="text-center text-slate-500 dark:text-slate-400 mb-10">Trusted by travellers around the world</p>
    <div class="grid gap-6 sm:grid-cols-3">
        @foreach([
            ['Best Price Guarantee', 'We match any lower price you find. Book with confidence every time.', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Free Cancellation', 'Most bookings are fully refundable. Change of plans? No problem at all.', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['24/7 Expert Support', 'Our travel experts are available around the clock to assist you.', 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'],
        ] as [$title, $desc, $icon])
        <div class="card p-6 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-navy/10 dark:bg-navy/20">
                <svg class="h-7 w-7 text-navy dark:text-navy-light" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $title }}</h3>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- ── CTA ── --}}
<section class="bg-gradient-to-r from-navy to-navy-light py-14">
    <div class="mx-auto max-w-3xl px-4 text-center">
        <h2 class="text-3xl font-bold text-white">Ready to Find Your Stay?</h2>
        <p class="mt-3 text-slate-300">Browse thousands of hotels and find the perfect match for your next trip.</p>
        <a href="{{ route('hotels.index') }}" class="btn-gold btn-lg mt-6 inline-flex">Browse All Hotels →</a>
    </div>
</section>
@endsection
