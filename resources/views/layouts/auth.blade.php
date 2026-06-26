<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data
      x-bind:class="{ 'dark': $store.theme.dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Welcome') — {{ config('app.name') }}</title>
    <script>if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-surface dark:bg-slate-950">
<div class="flex min-h-screen">

    {{-- Left panel — branding --}}
    <div class="hidden bg-navy lg:flex lg:w-1/2 xl:w-2/5 flex-col items-center justify-center p-12 relative overflow-hidden">
        {{-- Background pattern --}}
        <div class="absolute inset-0 bg-gradient-to-br from-navy-dark via-navy to-navy-light opacity-90"></div>
        <div class="absolute inset-0 opacity-5"
             style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>

        <div class="relative z-10 text-center">
            <a href="{{ route('home') }}" class="flex items-center justify-center gap-3 mb-8">
                <svg class="h-12 w-12 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-3xl font-bold text-white">{{ config('app.name') }}</span>
            </a>

            <p class="text-xl font-semibold text-white/90 mb-3">Your Perfect Stay Awaits</p>
            <p class="text-slate-300 max-w-xs">
                Discover and book world-class hotels at the best prices. From boutique gems to luxury resorts.
            </p>

            {{-- Feature list --}}
            <div class="mt-10 space-y-4 text-left">
                @foreach([
                    ['Best Price Guarantee', 'We match any lower price you find.'],
                    ['Free Cancellation', 'Flexible cancellation on most bookings.'],
                    ['24/7 Support', 'Our team is here whenever you need help.'],
                ] as [$title, $desc])
                <div class="flex items-start gap-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gold/20 mt-0.5">
                        <svg class="h-3.5 w-3.5 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $title }}</p>
                        <p class="text-xs text-slate-300">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right panel — form --}}
    <div class="flex flex-1 flex-col justify-center px-6 py-12 sm:px-12 lg:px-16 xl:px-20">
        {{-- Mobile logo --}}
        <div class="mb-8 flex items-center justify-between lg:hidden">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-navy dark:text-white">
                <svg class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ config('app.name') }}
            </a>
            <button @click="$store.theme.toggle()" class="btn-ghost btn-sm p-2 rounded-lg">
                <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg x-show="$store.theme.dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>
        </div>

        <div class="mx-auto w-full max-w-sm">
            @if($errors->any())
                <div class="mb-4 alert-error">
                    <ul class="flex-1 space-y-0.5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
