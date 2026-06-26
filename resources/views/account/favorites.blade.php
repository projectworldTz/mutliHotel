@extends('layouts.app')
@section('title', 'Saved Hotels')

@section('content')
<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="page-header">
        <h1 class="page-title">Saved Hotels</h1>
    </div>

    @if($favorites->isEmpty())
        <div class="card flex flex-col items-center justify-center py-20 text-center">
            <svg class="h-14 w-14 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">No saved hotels yet</h3>
            <p class="mt-1 text-sm text-slate-500">Tap the heart on any hotel to save it here.</p>
            <a href="{{ route('hotels.index') }}" class="btn-primary mt-5">Explore Hotels</a>
        </div>
    @else
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($favorites as $hotel)
        <div class="card group hover:shadow-xl transition">
            <div class="relative h-44 overflow-hidden bg-slate-200 dark:bg-slate-700">
                @if($hotel->featuredImage)
                    <img src="{{ $hotel->featuredImage->url }}" alt="{{ $hotel->name }}"
                         class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                @endif
                <form method="POST" action="{{ route('favorites.destroy', $hotel) }}"
                      class="absolute top-2 right-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit" title="Remove from saved"
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-rose-500 shadow hover:bg-rose-500 hover:text-white transition">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </form>
            </div>
            <div class="p-4">
                <a href="{{ route('hotels.show', $hotel) }}"
                   class="font-bold text-slate-900 dark:text-white hover:text-navy dark:hover:text-navy-light transition line-clamp-1">
                    {{ $hotel->name }}
                </a>
                <p class="mt-1 text-sm text-slate-500">{{ $hotel->city }}, {{ $hotel->country }}</p>
                <div class="mt-3 flex items-center justify-between">
                    @if($hotel->average_rating)
                        <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">★ {{ number_format($hotel->average_rating, 1) }}</span>
                    @else <span></span> @endif
                    <a href="{{ route('hotels.show', $hotel) }}" class="btn-primary btn-sm">Book Now</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection
