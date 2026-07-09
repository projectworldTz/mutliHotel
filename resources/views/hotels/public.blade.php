@extends('layouts.app')
@section('title', $hotel->name . ' — ' . __('Book Your Stay'))

@section('content')
@php
    $heroImages   = $hotel->images->pluck('url')->values();
    $allImages    = $heroImages;
    $reviewCount  = $hotel->approvedReviews->count();
    $avgRating    = $reviewCount ? round($hotel->approvedReviews->avg('rating'), 1) : 0;
    $starDist     = [5=>0,4=>0,3=>0,2=>0,1=>0];
    foreach ($hotel->approvedReviews as $r) $starDist[$r->rating] = ($starDist[$r->rating] ?? 0) + 1;
@endphp

<div x-data="hotelPage()" x-init="boot()">

{{-- ═══════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════ --}}
<section class="relative min-h-[88vh] flex flex-col justify-end overflow-hidden bg-slate-900">

    {{-- Background image carousel --}}
    @if($heroImages->isNotEmpty())
        <template x-for="(img, i) in heroImages" :key="i">
            <div class="absolute inset-0 transition-opacity duration-1000"
                 :class="heroIdx === i ? 'opacity-100' : 'opacity-0'">
                <img :src="img" class="h-full w-full object-cover" alt="">
            </div>
        </template>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-navy via-slate-800 to-slate-900"></div>
    @endif

    {{-- Gradient overlay --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/10 pointer-events-none"></div>

    {{-- Carousel dots --}}
    @if($heroImages->count() > 1)
    <div class="absolute bottom-64 left-1/2 -translate-x-1/2 flex gap-2 z-20">
        @foreach($heroImages as $i => $_)
        <button @click="heroIdx = {{ $i }}"
                :class="heroIdx === {{ $i }} ? 'bg-white w-6' : 'bg-white/40 w-2'"
                class="h-2 rounded-full transition-all duration-300"></button>
        @endforeach
    </div>
    @endif

    {{-- Carousel arrows --}}
    @if($heroImages->count() > 1)
    <button @click="heroIdx = (heroIdx - 1 + heroImages.length) % heroImages.length"
            class="absolute left-4 top-1/2 -translate-y-1/2 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 hover:bg-white/40 text-white backdrop-blur transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button @click="heroIdx = (heroIdx + 1) % heroImages.length"
            class="absolute right-4 top-1/2 -translate-y-1/2 z-20 flex h-10 w-10 items-center justify-center rounded-full bg-white/20 hover:bg-white/40 text-white backdrop-blur transition">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </button>
    @endif

    {{-- Hotel identity + quick info --}}
    <div class="relative z-10 mx-auto w-full max-w-7xl px-4 pb-6 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-6">

            <div class="text-white">
                {{-- Stars + Category --}}
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    <div class="flex gap-0.5">
                        @for($s = 1; $s <= 5; $s++)
                        <svg class="h-5 w-5 {{ $s <= $hotel->star_rating ? 'text-amber-400' : 'text-white/30' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    @if($hotel->category)
                    <span class="rounded-full bg-amber-400/20 border border-amber-400/40 px-3 py-0.5 text-sm font-medium text-amber-300">
                        {{ $hotel->category->name }}
                    </span>
                    @endif
                    @if($reviewCount)
                    <span class="flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-0.5 text-sm backdrop-blur">
                        <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <strong>{{ number_format($avgRating, 1) }}</strong>
                        <span class="text-white/70">({{ $reviewCount }} {{ Str::plural(__('review'), $reviewCount) }})</span>
                    </span>
                    @endif
                </div>

                <h1 class="text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl drop-shadow-sm">
                    {{ $hotel->name }}
                </h1>

                <p class="mt-3 flex items-center gap-2 text-lg text-white/80">
                    <svg class="h-5 w-5 shrink-0 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $hotel->city }}, {{ $hotel->country }}
                </p>

                {{-- Quick facts strip --}}
                <div class="mt-5 flex flex-wrap gap-3">
                    <span class="flex items-center gap-1.5 rounded-xl bg-white/15 backdrop-blur px-3 py-1.5 text-sm">
                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                        {{ __('Check-in') }}: <strong>{{ $hotel->check_in_time ?? '14:00' }}</strong>
                    </span>
                    <span class="flex items-center gap-1.5 rounded-xl bg-white/15 backdrop-blur px-3 py-1.5 text-sm">
                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        {{ __('Check-out') }}: <strong>{{ $hotel->check_out_time ?? '11:00' }}</strong>
                    </span>
                    @if($hotel->total_rooms)
                    <span class="flex items-center gap-1.5 rounded-xl bg-white/15 backdrop-blur px-3 py-1.5 text-sm">
                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        {{ $hotel->total_rooms }} {{ __('Rooms') }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Favourite --}}
            @auth
            <form method="POST" action="{{ route('favorites.toggle', $hotel) }}" class="z-20">
                @csrf
                <button type="submit"
                        class="flex items-center gap-2 rounded-xl border border-white/30 bg-white/10 backdrop-blur px-4 py-2.5 text-sm font-medium text-white hover:bg-white/20 transition">
                    <svg class="h-5 w-5 {{ auth()->user()->hasFavorited($hotel->id) ? 'text-rose-400 fill-rose-400' : '' }}"
                         fill="{{ auth()->user()->hasFavorited($hotel->id) ? 'currentColor' : 'none' }}"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    {{ auth()->user()->hasFavorited($hotel->id) ? __('Saved') : __('Save') }}
                </button>
            </form>
            @endauth
        </div>

        {{-- ── Availability Search Bar ── --}}
        <div class="mt-6 rounded-2xl border border-white/20 bg-black/40 backdrop-blur-md p-4 sm:p-5">
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-white/60 mb-1.5">{{ __('Check-in') }}</label>
                    <input type="date" x-model="checkIn" @change="calcNights()"
                           min="{{ now()->addDay()->toDateString() }}"
                           class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-2.5 text-sm text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-white/60 mb-1.5">{{ __('Check-out') }}</label>
                    <input type="date" x-model="checkOut" @change="calcNights()"
                           :min="checkIn || '{{ now()->addDays(2)->toDateString() }}'"
                           class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-2.5 text-sm text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-white/60 mb-1.5">{{ __('Guests') }}</label>
                    <select x-model="guests"
                            class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-2.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                        @for($g = 1; $g <= 10; $g++)
                        <option value="{{ $g }}" class="text-slate-900">{{ $g }} {{ $g === 1 ? __('Guest') : __('Guests') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex flex-col justify-end">
                    <button @click="search('{{ route('hotels.availability', $hotel) }}')"
                            :disabled="loading || !checkIn || !checkOut"
                            class="flex items-center justify-center gap-2 w-full rounded-xl bg-amber-400 hover:bg-amber-300 disabled:opacity-50 disabled:cursor-not-allowed px-6 py-2.5 text-sm font-bold text-slate-900 transition shadow-lg">
                        <svg x-show="!loading" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="loading ? '{{ __('Searching…') }}' : '{{ __('Search Rooms') }}'"></span>
                    </button>
                    <p x-show="nights > 0" class="mt-1.5 text-center text-xs text-white/60" x-text="nights + ' {{ __('night') }}' + (nights !== 1 ? 's' : '')"></p>
                </div>
            </div>
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     STICKY HOTEL NAV
════════════════════════════════════════════════════════ --}}
<nav class="sticky top-[61px] z-30 border-b border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex gap-1 overflow-x-auto scrollbar-none py-1">
            @foreach(array_filter([
                'overview'  => __('Overview'),
                'rooms'     => __('Rooms'),
                'amenities' => __('Amenities'),
                'gallery'   => __('Gallery'),
                'videos'    => $hotel->videos->isNotEmpty() ? __('Videos') : null,
                'reviews'   => __('Reviews'),
                'contact'   => __('Contact'),
            ]) as $key => $label)
            <button @click="scrollTo('{{ $key }}')"
                    :class="activeSection === '{{ $key }}'
                        ? 'text-navy dark:text-amber-400 border-b-2 border-navy dark:border-amber-400'
                        : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white border-b-2 border-transparent'"
                    class="shrink-0 px-4 py-3 text-sm font-semibold transition whitespace-nowrap {{ $key === 'videos' ? 'inline-flex items-center gap-1.5' : '' }}">
                @if($key === 'videos')
                <span class="relative inline-flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-amber-500">
                    <svg class="h-2.5 w-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11v11.78a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                    </svg>
                    <span class="absolute -top-0.5 -right-0.5 flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-rose-500"></span>
                    </span>
                </span>
                @endif
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>
</nav>

<div class="mx-auto max-w-7xl px-4 py-0 sm:px-6 lg:px-8">

{{-- ═══════════════════════════════════════════════════════
     OVERVIEW
════════════════════════════════════════════════════════ --}}
<section id="sec-overview" class="py-14 scroll-mt-32">
    <div class="grid gap-10 lg:grid-cols-3">

        {{-- Description --}}
        <div class="lg:col-span-2 space-y-8">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-4">{{ __('About') }} {{ $hotel->name }}</h2>
                @if($hotel->description)
                    <p class="text-base leading-relaxed text-slate-600 dark:text-slate-300">{{ $hotel->description }}</p>
                @elseif($hotel->short_description)
                    <p class="text-base leading-relaxed text-slate-600 dark:text-slate-300">{{ $hotel->short_description }}</p>
                @else
                    <p class="text-slate-400 italic">{{ __('No description provided.') }}</p>
                @endif
            </div>

            {{-- Highlights grid --}}
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800/30 p-4 text-center">
                    <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ $hotel->star_rating ?? '—' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Star Rating') }}</p>
                </div>
                <div class="rounded-2xl bg-navy/5 dark:bg-navy/20 border border-navy/10 dark:border-navy/30 p-4 text-center">
                    <p class="text-2xl font-extrabold text-navy dark:text-navy-light">{{ $reviewCount ?: '—' }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Guest Reviews') }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/30 p-4 text-center">
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ $hotel->roomTypes->count() }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Room Types') }}</p>
                </div>
                <div class="rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-rose-100 dark:border-rose-800/30 p-4 text-center">
                    <p class="text-2xl font-extrabold text-rose-500 dark:text-rose-400">{{ $hotel->amenities->count() }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Amenities') }}</p>
                </div>
            </div>

            {{-- Policy --}}
            @if($hotel->cancellation_policy)
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-5">
                <h3 class="flex items-center gap-2 font-semibold text-slate-900 dark:text-white mb-2">
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('Cancellation Policy') }}
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ $hotel->cancellation_policy }}</p>
            </div>
            @endif
        </div>

        {{-- Key Details sidebar --}}
        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 divide-y divide-slate-100 dark:divide-slate-700 overflow-hidden shadow-sm">
                <div class="px-5 py-4 bg-slate-50 dark:bg-slate-700/50">
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Hotel Details') }}</h3>
                </div>
                @foreach([
                    ['icon'=>'clock', 'label'=>__('Check-in'),  'value'=>($hotel->check_in_time  ?? '14:00')],
                    ['icon'=>'clock', 'label'=>__('Check-out'), 'value'=>($hotel->check_out_time ?? '11:00')],
                    ['icon'=>'phone', 'label'=>__('Phone'),     'value'=>$hotel->phone],
                    ['icon'=>'mail',  'label'=>__('Email'),     'value'=>$hotel->email],
                    ['icon'=>'globe', 'label'=>__('Website'),   'value'=>$hotel->website],
                ] as $d)
                @if(!empty($d['value']))
                <div class="flex items-center gap-3 px-5 py-3.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-navy/10 dark:bg-navy/30 text-navy dark:text-amber-400">
                        @if($d['icon']==='clock')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @elseif($d['icon']==='phone')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        @elseif($d['icon']==='mail')
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        @else
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $d['label'] }}</p>
                        @if($d['icon']==='globe')
                            <a href="{{ $d['value'] }}" target="_blank" class="text-sm font-medium text-navy dark:text-amber-400 hover:underline truncate block">{{ $d['value'] }}</a>
                        @elseif($d['icon']==='mail')
                            <a href="mailto:{{ $d['value'] }}" class="text-sm font-medium text-slate-800 dark:text-white hover:text-navy dark:hover:text-amber-400 truncate block">{{ $d['value'] }}</a>
                        @elseif($d['icon']==='phone')
                            <a href="tel:{{ $d['value'] }}" class="text-sm font-medium text-slate-800 dark:text-white hover:text-navy dark:hover:text-amber-400">{{ $d['value'] }}</a>
                        @else
                            <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $d['value'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     ROOMS
════════════════════════════════════════════════════════ --}}
<section id="sec-rooms" class="py-14 border-t border-slate-100 dark:border-slate-800 scroll-mt-32">

    {{-- Section header --}}
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                <span x-show="!searched">{{ __('Our Rooms') }}</span>
                <span x-show="searched" x-cloak>
                    {{ __('Available Rooms') }}
                    <span class="ml-2 text-base font-normal text-slate-500" x-text="'(' + checkIn + ' → ' + checkOut + ', ' + nights + ' {{ __('night') }}' + (nights !== 1 ? 's' : '') + ')'"></span>
                </span>
            </h2>
            <p x-show="!searched" class="mt-1 text-slate-500 dark:text-slate-400 text-sm">
                {{ __('Select dates above to check real-time availability and pricing.') }}
            </p>
            <p x-show="searched && availableRooms.length === 0 && unavailableRooms.length === 0" x-cloak
               class="mt-1 text-rose-500 text-sm font-medium">
                {{ __('No rooms configured for these dates — try different dates.') }}
            </p>
            <p x-show="searched && availableRooms.length === 0 && unavailableRooms.length > 0" x-cloak
               class="mt-1 text-amber-600 dark:text-amber-400 text-sm font-medium">
                {{ __('All rooms are fully booked for these dates. See below for when each type becomes available again.') }}
            </p>
        </div>
        <button x-show="searched" x-cloak @click="resetSearch()"
                class="flex items-center gap-2 rounded-xl border border-slate-300 dark:border-slate-600 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ __('Clear search') }}
        </button>
    </div>

    {{-- Default: all room types --}}
    <div x-show="!searched" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($hotel->roomTypes as $rt)
        @php $rtImg = $rt->images->first(); @endphp
        <div class="group rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
            {{-- Room image --}}
            <div class="relative h-52 overflow-hidden bg-slate-200 dark:bg-slate-700">
                @if($rtImg)
                    <img src="{{ $rtImg->url }}" alt="{{ $rt->name }}"
                         class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                @else
                    <div class="flex h-full items-center justify-center">
                        <svg class="h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                @endif
                {{-- Price badge --}}
                <div class="absolute top-3 right-3 rounded-xl bg-navy/90 backdrop-blur px-3 py-1.5 text-white">
                    <span class="text-lg font-extrabold">{{ money($rt->base_price) }}</span>
                    <span class="text-xs opacity-80">/{{ __('night') }}</span>
                </div>
                @if($rt->view_type)
                <div class="absolute top-3 left-3 rounded-lg bg-black/50 backdrop-blur px-2.5 py-1 text-xs font-medium text-white">
                    {{ ucfirst($rt->view_type) }} {{ __('View') }}
                </div>
                @endif
            </div>
            {{-- Room details --}}
            <div class="p-5">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $rt->name }}</h3>
                {{-- Bed + guest info --}}
                <div class="mt-2 flex flex-wrap gap-3 text-xs text-slate-500 dark:text-slate-400">
                    <span class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        {{ $rt->beds_count }} {{ Str::plural(__('bed'), $rt->beds_count) }} · {{ ucfirst($rt->bed_type) }}
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ __('Up to') }} {{ $rt->max_guests }} {{ Str::plural(__('guest'), $rt->max_guests) }}
                    </span>
                    @if($rt->size_sqm)
                    <span class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        {{ $rt->size_sqm }} m²
                    </span>
                    @endif
                </div>
                {{-- Room amenities --}}
                @if($rt->amenities->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach($rt->amenities->take(4) as $a)
                    <span class="rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs text-slate-600 dark:text-slate-300">{{ $a->name }}</span>
                    @endforeach
                    @if($rt->amenities->count() > 4)
                    <span class="rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs text-slate-500">+{{ $rt->amenities->count() - 4 }} {{ __('more') }}</span>
                    @endif
                </div>
                @endif
                {{-- Description --}}
                @if($rt->description)
                <p class="mt-3 text-xs text-slate-500 dark:text-slate-400 leading-relaxed line-clamp-2">{{ $rt->description }}</p>
                @endif
                {{-- CTA --}}
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('hotels.room.show', [$hotel, $rt]) }}"
                       class="flex-1 text-center rounded-xl border border-navy dark:border-amber-400 px-4 py-2.5 text-sm font-semibold text-navy dark:text-amber-400 hover:bg-navy hover:text-white dark:hover:bg-amber-400 dark:hover:text-slate-900 transition">
                        {{ __('View Details') }}
                    </a>
                    <button @click="checkIn = checkIn; scrollTo('rooms'); $nextTick(() => scrollToSearch())"
                            class="rounded-xl bg-navy hover:bg-navy/90 dark:bg-amber-400 dark:hover:bg-amber-300 dark:text-slate-900 px-4 py-2.5 text-sm font-bold text-white transition">
                        {{ __('Book') }}
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full py-12 text-center text-slate-400">
            <svg class="mx-auto h-12 w-12 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            {{ __('No room types configured yet.') }}
        </div>
        @endforelse
    </div>

    {{-- Availability results --}}
    <div x-show="searched" x-cloak class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        <template x-for="room in availableRooms" :key="room.id">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="relative bg-slate-100 dark:bg-slate-700 h-48 flex items-center justify-center">
                    <template x-if="room.image">
                        <img :src="room.image" :alt="room.name" class="h-full w-full object-cover">
                    </template>
                    <svg x-show="!room.image" class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <div class="absolute top-3 right-3 rounded-xl bg-navy/90 text-white px-3 py-1.5 backdrop-blur">
                        <span class="text-lg font-extrabold" x-text="'{{ config('app.currency') }} ' + room.nightly_rate"></span>
                        <span class="text-xs opacity-80">/{{ __('night') }}</span>
                    </div>
                </div>
                <div class="p-5">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white" x-text="room.name"></h3>
                    <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 font-semibold" x-text="room.available_count + ' {{ __('room') }}' + (room.available_count !== 1 ? 's' : '') + ' {{ __('left') }}'"></p>
                    <div class="mt-3 rounded-xl bg-slate-50 dark:bg-slate-700 p-3 flex justify-between items-center">
                        <div>
                            <p class="text-xs text-slate-500">{{ __('Total for') }} <span x-text="nights"></span> {{ __('night') }}<span x-show="nights !== 1">s</span></p>
                            <p class="text-xl font-extrabold text-navy dark:text-amber-400" x-text="'{{ config('app.currency') }} ' + (room.nightly_rate * nights).toLocaleString()"></p>
                        </div>
                        @auth
                        <form method="POST" action="{{ route('booking.cart.store') }}">
                            @csrf
                            <input type="hidden" name="room_type_id" :value="room.id">
                            <input type="hidden" name="check_in" :value="checkIn">
                            <input type="hidden" name="check_out" :value="checkOut">
                            <input type="hidden" name="guests" :value="guests">
                            <button type="submit" class="rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold text-sm px-5 py-2.5 transition shadow">
                                {{ __('Reserve') }}
                            </button>
                        </form>
                        @else
                        <a href="{{ route('login') }}" class="rounded-xl bg-navy dark:bg-amber-400 text-white dark:text-slate-900 font-bold text-sm px-5 py-2.5 hover:opacity-90 transition">
                            {{ __('Sign in') }}
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </template>

        <template x-if="searched && availableRooms.length === 0 && unavailableRooms.length === 0">
            <div class="col-span-full py-16 text-center">
                <svg class="mx-auto h-14 w-14 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">{{ __('No rooms available') }}</p>
                <p class="mt-1 text-sm text-slate-500">{{ __('Try different dates or fewer guests.') }}</p>
            </div>
        </template>
    </div>

    {{-- Fully-booked room types with next-available date --}}
    <div x-show="searched && unavailableRooms.length > 0" x-cloak class="mt-8">
        <h3 class="text-base font-semibold text-slate-700 dark:text-slate-300 mb-4 flex items-center gap-2">
            <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            {{ __('Fully booked for your dates') }}
        </h3>
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            <template x-for="room in unavailableRooms" :key="room.id">
                <div class="relative rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden shadow-sm">

                    {{-- Image with "Fully Booked" overlay --}}
                    <div class="relative h-44 bg-slate-100 dark:bg-slate-700 flex items-center justify-center overflow-hidden">
                        <template x-if="room.image">
                            <img :src="room.image" :alt="room.name" class="h-full w-full object-cover grayscale">
                        </template>
                        <svg x-show="!room.image" class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                            <span class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white shadow-lg tracking-wide">
                                {{ __('Fully Booked') }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white" x-text="room.name"></h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1"
                           x-text="room.max_guests + ' {{ __('guests max') }} · ' + room.bed_type"></p>

                        {{-- Next available notice --}}
                        <div class="mt-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-3">
                            <template x-if="room.next_available">
                                <div class="flex items-start gap-2">
                                    <svg class="h-4 w-4 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">{{ __('Next available from') }}</p>
                                        <p class="text-sm font-bold text-amber-800 dark:text-amber-300"
                                           x-text="formatDate(room.next_available)"></p>
                                        <button @click="checkIn = room.next_available; calcNights(); scrollToHero()"
                                                class="mt-2 text-xs font-semibold text-navy dark:text-amber-400 hover:underline">
                                            {{ __('Search from this date') }} →
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <template x-if="!room.next_available">
                                <p class="text-xs text-slate-500 dark:text-slate-400 italic">
                                    {{ __('Contact the hotel for availability on these dates.') }}
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     AMENITIES
════════════════════════════════════════════════════════ --}}
@if($hotel->amenities->isNotEmpty())
<section id="sec-amenities" class="py-14 border-t border-slate-100 dark:border-slate-800 scroll-mt-32">
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-8">{{ __("What's Included") }}</h2>
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($hotel->amenities as $amenity)
        <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-3.5 hover:border-navy/30 dark:hover:border-amber-400/30 transition group">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-navy/10 dark:bg-amber-400/10 text-navy dark:text-amber-400 group-hover:bg-navy/20 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $amenity->name }}</span>
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     GALLERY
════════════════════════════════════════════════════════ --}}
@if($hotel->images->count() > 0)
<section id="sec-gallery" class="py-14 border-t border-slate-100 dark:border-slate-800 scroll-mt-32">
    <div class="flex items-end justify-between mb-8">
        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('Photo Gallery') }}</h2>
        <span class="text-sm text-slate-500">{{ $hotel->images->count() }} {{ __('photos') }}</span>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
        @foreach($hotel->images as $idx => $img)
        <button @click="openLightbox({{ $idx }})"
                class="group relative overflow-hidden rounded-2xl bg-slate-200 dark:bg-slate-700 aspect-square">
            <img src="{{ $img->url }}" alt="{{ __('Hotel photo') }} {{ $idx + 1 }}"
                 class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500">
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 flex items-center justify-center transition-all duration-300">
                <svg class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                </svg>
            </div>
            @if($idx === 0)
            <div class="absolute top-2 left-2 rounded-lg bg-black/50 backdrop-blur px-2 py-0.5 text-xs font-medium text-white">{{ __('Cover') }}</div>
            @endif
        </button>
        @endforeach
    </div>

    {{-- Lightbox --}}
    <div x-show="lightbox" x-cloak @keydown.escape.window="lightbox = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-sm"
         x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <button @click="lightbox = false" class="absolute top-4 right-4 text-white/70 hover:text-white p-2">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <button @click="lightboxIdx = (lightboxIdx - 1 + allImages.length) % allImages.length"
                class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white p-2">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <img :src="allImages[lightboxIdx]" class="max-h-[85vh] max-w-[90vw] rounded-xl object-contain shadow-2xl">
        <button @click="lightboxIdx = (lightboxIdx + 1) % allImages.length"
                class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white p-2">
            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
        <div class="absolute bottom-4 text-white/60 text-sm" x-text="(lightboxIdx + 1) + ' / ' + allImages.length"></div>
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     VIDEOS
════════════════════════════════════════════════════════ --}}
@if($hotel->videos->isNotEmpty())
<section id="sec-videos" class="py-14 border-t border-slate-100 dark:border-slate-800 scroll-mt-32">
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-8">{{ __('Videos') }}</h2>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        @foreach($hotel->videos as $video)
        <div class="rounded-2xl overflow-hidden bg-slate-900 shadow-lg">
            <div class="aspect-video">
                @if($video->isUpload())
                <video src="{{ $video->url }}" controls class="h-full w-full object-cover"></video>
                @else
                <iframe src="{{ $video->embed_url }}" class="h-full w-full" allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                @endif
            </div>
            @if($video->title)
            <p class="px-4 py-3 text-sm font-medium text-white">{{ $video->title }}</p>
            @endif
        </div>
        @endforeach
    </div>
</section>
@endif

{{-- ═══════════════════════════════════════════════════════
     REVIEWS
════════════════════════════════════════════════════════ --}}
<section id="sec-reviews" class="py-14 border-t border-slate-100 dark:border-slate-800 scroll-mt-32">
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-8">{{ __('Guest Reviews') }}</h2>

    @if($hotel->approvedReviews->isNotEmpty())
    <div class="grid gap-8 lg:grid-cols-3">

        {{-- Rating summary --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 text-center h-fit shadow-sm">
            <div class="text-6xl font-extrabold text-navy dark:text-amber-400 leading-none">{{ number_format($avgRating, 1) }}</div>
            <div class="flex justify-center gap-0.5 mt-2">
                @for($s = 1; $s <= 5; $s++)
                <svg class="h-5 w-5 {{ $s <= round($avgRating) ? 'text-amber-400' : 'text-slate-200 dark:text-slate-600' }}"
                     fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                @endfor
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $reviewCount }} {{ Str::plural(__('review'), $reviewCount) }}</p>

            <div class="mt-5 space-y-2">
                @foreach([5,4,3,2,1] as $star)
                @php $cnt = $starDist[$star] ?? 0; $pct = $reviewCount ? round($cnt / $reviewCount * 100) : 0; @endphp
                <div class="flex items-center gap-2 text-xs">
                    <span class="w-3 text-right text-slate-600 dark:text-slate-400">{{ $star }}</span>
                    <svg class="h-3 w-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <div class="flex-1 rounded-full bg-slate-100 dark:bg-slate-700 h-2 overflow-hidden">
                        <div class="h-2 rounded-full bg-amber-400 transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="w-6 text-slate-400">{{ $pct }}%</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Review cards --}}
        <div class="lg:col-span-2 space-y-4">
            @foreach($hotel->approvedReviews->take(8) as $review)
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-navy to-slate-600 text-sm font-bold text-white shadow">
                            {{ strtoupper(substr($review->user->name ?? 'G', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $review->user->name ?? __('Guest') }}</p>
                            <p class="text-xs text-slate-400">{{ $review->created_at->format('M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-0.5">
                        @for($s = 1; $s <= 5; $s++)
                        <svg class="h-4 w-4 {{ $s <= $review->rating ? 'text-amber-400' : 'text-slate-200 dark:text-slate-600' }}"
                             fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                </div>
                @if($review->title)
                <p class="mt-3 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $review->title }}</p>
                @endif
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ Str::limit($review->comment, 280) }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="py-16 text-center rounded-2xl border border-dashed border-slate-300 dark:border-slate-700">
        <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        <p class="text-slate-500 dark:text-slate-400">{{ __('No reviews yet. Be the first to stay and share your experience!') }}</p>
    </div>
    @endif
</section>

{{-- ═══════════════════════════════════════════════════════
     CONTACT / LOCATION
════════════════════════════════════════════════════════ --}}
<section id="sec-contact" class="py-14 border-t border-slate-100 dark:border-slate-800 scroll-mt-32">
    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-8">{{ __('Location & Contact') }}</h2>

    <div class="grid gap-8 lg:grid-cols-2">

        {{-- Map placeholder / embed --}}
        <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 shadow-sm bg-slate-100 dark:bg-slate-800 min-h-72 flex flex-col">
            @if($hotel->latitude && $hotel->longitude)
            <iframe
                src="https://maps.google.com/maps?q={{ $hotel->latitude }},{{ $hotel->longitude }}&z=15&output=embed"
                class="w-full flex-1 min-h-72 border-0"
                allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            @else
            <div class="flex-1 flex flex-col items-center justify-center gap-4 p-8 text-center">
                <svg class="h-16 w-16 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <div>
                    <p class="font-semibold text-slate-700 dark:text-slate-300">{{ $hotel->address }}</p>
                    <p class="text-slate-500">{{ $hotel->city }}@if($hotel->state), {{ $hotel->state }}@endif, {{ $hotel->country }}</p>
                    @if($hotel->postal_code)<p class="text-sm text-slate-400">{{ $hotel->postal_code }}</p>@endif
                </div>
                <a href="https://www.google.com/maps/search/{{ urlencode($hotel->name . ' ' . $hotel->address . ' ' . $hotel->city) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 rounded-xl bg-navy dark:bg-amber-400 px-5 py-2.5 text-sm font-semibold text-white dark:text-slate-900 hover:opacity-90 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ __('Get Directions') }}
                </a>
            </div>
            @endif
        </div>

        {{-- Contact details + quick booking CTA --}}
        <div class="space-y-5">
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 space-y-4 shadow-sm">
                <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Reach Us') }}</h3>

                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700 text-navy dark:text-amber-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Address') }}</p>
                        <p class="text-sm text-slate-800 dark:text-white font-medium">{{ $hotel->address }}</p>
                        <p class="text-sm text-slate-500">{{ $hotel->city }}@if($hotel->state), {{ $hotel->state }}@endif, {{ $hotel->country }}</p>
                    </div>
                </div>

                @if($hotel->phone)
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700 text-navy dark:text-amber-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Phone') }}</p>
                        <a href="tel:{{ $hotel->phone }}" class="text-sm font-semibold text-navy dark:text-amber-400 hover:underline">{{ $hotel->phone }}</a>
                    </div>
                </div>
                @endif

                @if($hotel->email)
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700 text-navy dark:text-amber-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Email') }}</p>
                        <a href="mailto:{{ $hotel->email }}" class="text-sm font-semibold text-navy dark:text-amber-400 hover:underline">{{ $hotel->email }}</a>
                    </div>
                </div>
                @endif

                @if($hotel->website)
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700 text-navy dark:text-amber-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('Website') }}</p>
                        <a href="{{ $hotel->website }}" target="_blank" class="text-sm font-semibold text-navy dark:text-amber-400 hover:underline">{{ $hotel->website }}</a>
                    </div>
                </div>
                @endif
            </div>

            {{-- Final booking CTA card --}}
            <div class="rounded-2xl bg-gradient-to-br from-navy to-slate-700 p-6 text-white shadow-xl">
                <h3 class="text-lg font-bold mb-1">{{ __('Ready to book?') }}</h3>
                <p class="text-sm text-white/70 mb-4">{{ __('Use the search above or click below to explore available rooms.') }}</p>
                <button @click="scrollTo('rooms')"
                        class="w-full rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold py-3 text-sm transition shadow">
                    {{ __('View Available Rooms') }} →
                </button>
                @guest
                <p class="mt-3 text-xs text-white/50 text-center">
                    <a href="{{ route('register') }}" class="underline hover:text-white">{{ __('Create an account') }}</a> {{ __('to save your booking') }}
                </p>
                @endguest
            </div>
        </div>

    </div>
</section>

</div>{{-- /max-w-7xl --}}
</div>{{-- /x-data --}}

@endsection

@push('scripts')
<script>
function hotelPage() {
    return {
        activeSection: 'overview',
        heroIdx: 0,
        heroImages: [],
        allImages: [],
        checkIn: '',
        checkOut: '',
        guests: 2,
        nights: 0,
        loading: false,
        searched: false,
        availableRooms: [],
        unavailableRooms: [],
        lightbox: false,
        lightboxIdx: 0,

        boot() {
            this.heroImages = @json($heroImages->values());
            this.allImages  = @json($allImages->values());

            // Default dates
            const d1 = new Date(); d1.setDate(d1.getDate() + 1);
            const d2 = new Date(); d2.setDate(d2.getDate() + 2);
            this.checkIn  = d1.toISOString().split('T')[0];
            this.checkOut = d2.toISOString().split('T')[0];
            this.calcNights();

            // Auto-advance hero carousel
            if (this.heroImages.length > 1) {
                setInterval(() => {
                    this.heroIdx = (this.heroIdx + 1) % this.heroImages.length;
                }, 5000);
            }

            // Scrollspy
            window.addEventListener('scroll', () => this.scrollspy(), { passive: true });
        },

        scrollspy() {
            const sections = ['overview','rooms','amenities','gallery','videos','reviews','contact'];
            for (const id of [...sections].reverse()) {
                const el = document.getElementById('sec-' + id);
                if (el && el.getBoundingClientRect().top <= 130) {
                    this.activeSection = id;
                    return;
                }
            }
            this.activeSection = 'overview';
        },

        scrollTo(id) {
            const el = document.getElementById('sec-' + id);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        calcNights() {
            if (this.checkIn && this.checkOut) {
                const ms = new Date(this.checkOut) - new Date(this.checkIn);
                this.nights = Math.max(0, Math.round(ms / 86400000));
            }
        },

        async search(apiUrl) {
            if (!this.checkIn || !this.checkOut) return;
            this.calcNights();
            this.loading = true;
            try {
                const url = new URL(apiUrl, window.location.origin);
                url.searchParams.set('check_in', this.checkIn);
                url.searchParams.set('check_out', this.checkOut);
                url.searchParams.set('guests', this.guests);
                const res = await fetch(url);
                const data = await res.json();
                this.availableRooms   = data.room_types        || [];
                this.unavailableRooms = data.unavailable_types || [];
                this.searched = true;
            } catch(e) { console.error(e); }
            this.loading = false;
            setTimeout(() => this.scrollTo('rooms'), 120);
        },

        resetSearch() {
            this.searched         = false;
            this.availableRooms   = [];
            this.unavailableRooms = [];
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr + 'T00:00:00');
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        },

        scrollToHero() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        openLightbox(idx) {
            this.lightboxIdx = idx;
            this.lightbox = true;
        },

        scrollToSearch() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };
}
</script>
@endpush
