@extends('layouts.app')
@section('title', __('Browse Hotels'))

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('Browse Hotels') }}</h1>
        <p class="mt-1 text-slate-500 dark:text-slate-400">
            {{ $hotels->total() }} {{ Str::plural(__('hotel'), $hotels->total()) }} {{ __('found') }}
            @if(!empty($filters['search'])) {{ __('for') }} "<em>{{ $filters['search'] }}</em>" @endif
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-4">
        {{-- ── Filters sidebar ── --}}
        <aside x-data="{ open: false }" class="lg:col-span-1">
            <button @click="open = !open"
                    class="flex w-full items-center justify-between rounded-xl bg-white px-4 py-3 shadow-sm ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700 lg:hidden">
                <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ __('Filters') }}</span>
                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
            </button>

            <div x-show="open" class="mt-3 lg:hidden lg:mt-0"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2">
                @include('hotels._filters')
            </div>
            <div class="hidden lg:block">
                @include('hotels._filters')
            </div>
        </aside>

        {{-- ── Results ── --}}
        <div class="lg:col-span-3">
            {{-- Sort bar --}}
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ __('Showing') }} {{ $hotels->count() }} {{ __('of') }} {{ $hotels->total() }}</span>
                <form method="GET" action="{{ route('hotels.index') }}" id="sort-form">
                    @foreach($filters as $key => $val)
                        @if($key !== 'sort')
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <select name="sort" onchange="document.getElementById('sort-form').submit()"
                            class="form-select w-auto py-2 text-sm">
                        <option value="featured" {{ ($filters['sort'] ?? '') === 'featured' ? 'selected' : '' }}>{{ __('Featured') }}</option>
                        <option value="rating"    {{ ($filters['sort'] ?? '') === 'rating'   ? 'selected' : '' }}>{{ __('Top Rated') }}</option>
                        <option value="price_asc" {{ ($filters['sort'] ?? '') === 'price_asc'? 'selected' : '' }}>{{ __('Price') }} ↑</option>
                        <option value="price_desc"{{ ($filters['sort'] ?? '') === 'price_desc'? 'selected' : '' }}>{{ __('Price') }} ↓</option>
                        <option value="newest"    {{ ($filters['sort'] ?? '') === 'newest'   ? 'selected' : '' }}>{{ __('Newest') }}</option>
                    </select>
                </form>
            </div>

            @if($hotels->isEmpty())
                <div class="card flex flex-col items-center justify-center py-20 text-center" data-reveal>
                    <svg class="h-14 w-14 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('No hotels found') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Try adjusting your filters or search terms.') }}</p>
                    <a href="{{ route('hotels.index') }}" class="btn-outline btn-sm mt-4">{{ __('Clear Filters') }}</a>
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" data-stagger="70">
                    @foreach($hotels as $hotel)
                    <a href="{{ route('hotels.show', $hotel) }}"
                       class="card group hover:shadow-2xl transition-all duration-300"
                       data-tilt>
                        <div class="relative h-48 overflow-hidden rounded-t-2xl bg-slate-200 dark:bg-slate-700">
                            @if($hotel->featuredImage)
                                <img src="{{ $hotel->featuredImage->url }}" alt="{{ $hotel->name }}"
                                     class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500"
                                     data-lazy>
                            @else
                                <div class="flex h-full items-center justify-center">
                                    <svg class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            @endif
                            {{-- Star badge --}}
                            <div class="absolute top-2 left-2 flex items-center gap-0.5 rounded-full bg-white/90 dark:bg-slate-800/90 backdrop-blur px-2 py-1 shadow">
                                @for($i = 1; $i <= $hotel->star_rating; $i++)
                                <svg class="h-3 w-3 text-gold" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                @endfor
                            </div>
                            {{-- Rating badge top-right --}}
                            @if($hotel->average_rating)
                            <div class="absolute top-2 right-2 rounded-full bg-emerald-500 text-white text-xs font-bold px-2 py-1 shadow">
                                ★ {{ number_format($hotel->average_rating, 1) }}
                            </div>
                            @endif
                            {{-- Gradient overlay on hover --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-navy/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <div class="absolute bottom-3 left-3 right-3 text-white text-sm font-semibold opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                                {{ __('View hotel') }} →
                            </div>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-slate-900 dark:text-white group-hover:text-navy dark:group-hover:text-navy-light transition-colors line-clamp-1">
                                {{ $hotel->name }}
                            </h3>
                            <p class="mt-1 flex items-center gap-1 text-xs text-slate-500 dark:text-slate-400">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $hotel->city }}, {{ $hotel->country }}
                            </p>
                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-xs text-slate-400">{{ $hotel->category->name ?? '' }}</span>
                                <span class="text-sm font-bold text-navy dark:text-navy-light">
                                    @if($hotel->roomTypes->isNotEmpty())
                                        <span class="text-xs font-normal text-slate-400">{{ __('from') }}</span>
                                        {{ money($hotel->roomTypes->min('base_price')) }}<span class="text-xs font-normal text-slate-400">/{{ __('night') }}</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $hotels->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
