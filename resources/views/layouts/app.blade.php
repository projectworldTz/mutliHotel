<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data
      x-bind:class="{ 'dark': $store.theme.dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — {{ config('app.name') }}</title>
    {{-- Prevent flash of wrong theme --}}
    <script>
        if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-surface dark:bg-slate-950">

{{-- ── Navbar ── --}}
<header x-data="{ open: false, userOpen: false }"
        class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/95 backdrop-blur-sm dark:border-slate-700/50 dark:bg-slate-900/95">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-navy dark:text-white">
            <svg class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            {{ config('app.name') }}
        </a>

        {{-- Desktop navigation --}}
        <nav class="hidden items-center gap-1 md:flex">
            <a href="{{ route('hotels.index') }}"
               class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-navy dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                {{ __('Hotels') }}
            </a>
            <a href="{{ route('blog.index') }}"
               class="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-navy dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                {{ __('Blog') }}
            </a>
        </nav>

        {{-- Right controls --}}
        <div class="flex items-center gap-1">

            @auth
            {{-- Favourites --}}
            <a href="{{ route('favorites.index') }}"
               class="btn-ghost btn-sm p-2 rounded-lg" title="{{ __('Saved Hotels') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </a>

            {{-- Cart --}}
            <a href="{{ route('booking.cart') }}"
               class="relative btn-ghost btn-sm p-2 rounded-lg" title="{{ __('Reservation Cart') }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                @php
                    try {
                        $cartCount = \App\Models\ReservationCart::where('user_id', auth()->id())
                            ->withCount('items')->first()?->items_count ?? 0;
                    } catch(\Exception $e) { $cartCount = 0; }
                @endphp
                @if($cartCount > 0)
                    <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-gold text-[10px] font-bold text-white">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
            @endauth

            {{-- Dark mode toggle --}}
            <button @click="$store.theme.toggle()"
                    class="btn-ghost btn-sm p-2 rounded-lg" title="{{ __('Toggle dark mode') }}">
                <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg x-show="$store.theme.dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            {{-- Language switcher --}}
            <x-language-switcher />

            @auth
            {{-- User dropdown --}}
            <div class="relative" x-data="{ userOpen: false }">
                <button @click="userOpen = !userOpen"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    <span class="hidden sm:inline">{{ Str::limit(auth()->user()->name, 12) }}</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="userOpen" @click.outside="userOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-1 w-52 origin-top-right rounded-xl bg-white py-1 shadow-lg ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700">{{ __('My Bookings') }}</a>
                    <a href="{{ route('account.profile') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700">{{ __('Profile') }}</a>
                    @can('access-admin')
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gold hover:bg-slate-50 dark:hover:bg-slate-700">{{ __('Admin Panel') }}</a>
                    @endcan
                    @can('access-owner')
                        <a href="{{ route('owner.dashboard') }}" class="block px-4 py-2 text-sm text-navy dark:text-navy-light hover:bg-slate-50 dark:hover:bg-slate-700">{{ __('Owner Panel') }}</a>
                    @endcan
                    <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-slate-50 dark:text-rose-400 dark:hover:bg-slate-700">
                            {{ __('Log out') }}
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="btn-ghost btn-sm">{{ __('Sign in') }}</a>
            <a href="{{ route('register') }}" class="btn-primary btn-sm">{{ __('Sign up') }}</a>
            @endauth

            {{-- Mobile menu button --}}
            <button @click="open = !open"
                    class="btn-ghost btn-sm p-2 rounded-lg md:hidden">
                <svg x-show="!open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="open" class="border-t border-slate-200 px-4 pb-3 pt-2 dark:border-slate-700 md:hidden">
        <a href="{{ route('hotels.index') }}" class="block py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Hotels') }}</a>
        <a href="{{ route('blog.index') }}" class="block py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Blog') }}</a>
        @auth
            <a href="{{ route('dashboard') }}" class="block py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('My Bookings') }}</a>
            <a href="{{ route('favorites.index') }}" class="block py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Saved Hotels') }}</a>
        @endauth
    </div>
</header>

{{-- ── Flash messages ── --}}
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
             class="mt-4 alert-success" role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="flex-1">{{ session('success') }}</p>
            <button @click="show = false" class="ml-auto shrink-0 opacity-60 hover:opacity-100">✕</button>
        </div>
    @endif
    @if(session('warning'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
             class="mt-4 alert-warning" role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="flex-1">{{ session('warning') }}</p>
            <button @click="show = false" class="ml-auto shrink-0 opacity-60 hover:opacity-100">✕</button>
        </div>
    @endif
    @if($errors->any())
        <div class="mt-4 alert-error" role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <ul class="flex-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

{{-- ── Main content ── --}}
<main>
    @yield('content')
</main>

{{-- ── Footer ── --}}
<footer class="mt-20 bg-navy text-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <div class="flex items-center gap-2 text-lg font-bold">
                    <svg class="h-6 w-6 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    {{ config('app.name') }}
                </div>
                <p class="mt-3 text-sm text-slate-300">
                    {{ __('Discover and book the finest hotels and resorts worldwide. Your perfect stay is just a click away.') }}
                </p>
            </div>
            <div>
                <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gold">{{ __('Explore') }}</h4>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li><a href="{{ route('hotels.index') }}" class="hover:text-white transition">{{ __('Browse Hotels') }}</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white transition">{{ __('Travel Blog') }}</a></li>
                </ul>
            </div>
            <div>
                <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gold">{{ __('Account') }}</h4>
                <ul class="space-y-2 text-sm text-slate-300">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="hover:text-white transition">{{ __('My Bookings') }}</a></li>
                        <li><a href="{{ route('favorites.index') }}" class="hover:text-white transition">{{ __('Saved Hotels') }}</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">{{ __('Sign in') }}</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">{{ __('Create Account') }}</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gold">{{ __('Language') }}</h4>
                <div class="space-y-2 text-sm text-slate-300">
                    <a href="{{ route('language.switch', 'en') }}"
                       class="flex items-center gap-2 hover:text-white transition {{ app()->getLocale() === 'en' ? 'text-white font-semibold' : '' }}">
                        🇬🇧 English
                    </a>
                    <a href="{{ route('language.switch', 'sw') }}"
                       class="flex items-center gap-2 hover:text-white transition {{ app()->getLocale() === 'sw' ? 'text-white font-semibold' : '' }}">
                        🇹🇿 Kiswahili
                    </a>
                </div>
            </div>
        </div>
        <div class="mt-8 border-t border-white/10 pt-6 text-center text-xs text-slate-400">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</footer>

@stack('scripts')
</body>
</html>
