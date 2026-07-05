<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data
      x-bind:class="{ 'dark': $store.theme.dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Reception') — {{ $assignedHotel->name ?? config('app.name') }}</title>
    <script>if (localStorage.getItem('theme') !== 'light') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-slate-100 dark:bg-slate-950" x-data="{ sidebarOpen: false }">
<div class="flex min-h-screen">

    {{-- ── Receptionist Sidebar ── --}}
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-navy transition-transform duration-300 ease-in-out
                  lg:translate-x-0 lg:static lg:flex"
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">

        <div class="flex h-16 shrink-0 items-center gap-2 border-b border-white/10 px-5">
            <svg class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-white">{{ $assignedHotel->name ?? config('app.name') }}</p>
                <span class="rounded-full bg-gold/20 px-2 py-0.5 text-[10px] font-semibold uppercase text-gold">{{ __('Reception') }}</span>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
            @php $route = Route::currentRouteName(); @endphp

            <a href="{{ route('receptionist.dashboard') }}"
               class="{{ str_starts_with($route ?? '', 'receptionist.dashboard') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Dashboard') }}
            </a>

            @php
                $navPending  = isset($assignedHotel)
                    ? \App\Models\Booking::where('hotel_id', $assignedHotel->id)
                        ->where('status', \App\Models\Booking::STATUS_PENDING)
                        ->count()
                    : 0;
                $navArrivals = isset($assignedHotel)
                    ? \App\Models\Booking::where('hotel_id', $assignedHotel->id)
                        ->where('check_in', now()->toDateString())
                        ->whereIn('status', [\App\Models\Booking::STATUS_CONFIRMED, \App\Models\Booking::STATUS_CHECKED_IN])
                        ->count()
                    : 0;
            @endphp
            <a href="{{ route('receptionist.bookings.index') }}"
               class="{{ str_starts_with($route ?? '', 'receptionist.bookings') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="flex-1">{{ __('Bookings') }}</span>
                @if($navPending > 0)
                    <span class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 text-[10px] font-bold text-slate-900">
                        {{ $navPending > 99 ? '99+' : $navPending }}
                    </span>
                @endif
                @if($navArrivals > 0)
                    <span class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-emerald-500 px-1.5 text-[10px] font-bold text-white">
                        {{ $navArrivals > 99 ? '99+' : $navArrivals }}
                    </span>
                @endif
            </a>

            <a href="{{ route('receptionist.bookings.create') }}"
               class="{{ ($route ?? '') === 'receptionist.bookings.create' ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('New Booking') }}
            </a>

            <a href="{{ route('receptionist.availability') }}"
               class="{{ ($route ?? '') === 'receptionist.availability' ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('Availability') }}
            </a>

            <a href="{{ route('receptionist.guests.index') }}"
               class="{{ str_starts_with($route ?? '', 'receptionist.guests') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ __('Guests') }}
            </a>

            @if(isset($assignedHotel) && $assignedHotel->hasFeature('housekeeping'))
            <a href="{{ route('receptionist.housekeeping.index') }}"
               class="{{ str_starts_with($route ?? '', 'receptionist.housekeeping') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="flex-1">{{ __('Housekeeping') }}</span>
                @php
                    $navHkPending = \App\Models\HousekeepingTask::forHotel($assignedHotel->id)->pending()->count();
                @endphp
                @if($navHkPending > 0)
                <span class="ml-auto inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 text-[10px] font-bold text-slate-900">
                    {{ $navHkPending > 99 ? '99+' : $navHkPending }}
                </span>
                @endif
            </a>
            @endif

            @if(isset($assignedHotel) && $assignedHotel->hasFeature('inventory_management'))
            <a href="{{ route('receptionist.inventory.index') }}"
               class="{{ str_starts_with($route ?? '', 'receptionist.inventory') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>{{ __('Inventory') }}</span>
            </a>
            @endif
        </nav>

        <div class="shrink-0 border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gold/20 text-sm font-bold text-gold">
                    {{ strtoupper(substr(auth()->user()->name ?? 'R', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name ?? 'Receptionist' }}</p>
                    <p class="truncate text-xs text-slate-400">{{ __('Receptionist') }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="{{ __('Logout') }}" class="text-slate-400 hover:text-rose-400 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 dark:border-slate-700 dark:bg-slate-900 sm:px-6">
            <button @click="sidebarOpen = true" class="btn-ghost p-2 rounded-lg lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="flex-1 text-base font-semibold text-slate-900 dark:text-white">
                @yield('page-title', __('Reception'))
            </h1>

            {{-- Dark mode --}}
            <button @click="$store.theme.toggle()" class="btn-ghost btn-sm p-2 rounded-lg" title="{{ __('Toggle dark mode') }}">
                <svg x-show="!$store.theme.dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg x-show="$store.theme.dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            {{-- Language switcher --}}
            <x-language-switcher />
        </header>

        <div class="px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mt-4 alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mt-4 alert-error">
                    @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
                </div>
            @endif
        </div>

        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
