<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data
      x-bind:class="{ 'dark': $store.theme.dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Accounts') — {{ $assignedHotel->name ?? config('app.name') }}</title>
    <script>if (localStorage.getItem('theme') !== 'light') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-slate-100 dark:bg-slate-950" x-data="{ sidebarOpen: false }">
<div class="flex min-h-screen">

    {{-- ── Accountant Sidebar ── --}}
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-navy transition-transform duration-300 ease-in-out
                  lg:translate-x-0 lg:static lg:flex"
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">

        <div class="flex h-16 shrink-0 items-center gap-2 border-b border-white/10 px-5">
            <svg class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8h6m-6 4h6m-6 4h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
            </svg>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-white">{{ $assignedHotel->name ?? config('app.name') }}</p>
                <span class="rounded-full bg-gold/20 px-2 py-0.5 text-[10px] font-semibold uppercase text-gold">{{ __('Accounts') }}</span>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
            @php $route = Route::currentRouteName(); @endphp

            <a href="{{ route('accountant.dashboard') }}"
               class="{{ str_starts_with($route ?? '', 'accountant.dashboard') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Dashboard') }}
            </a>

            <a href="{{ route('accountant.invoices.index') }}"
               class="{{ str_starts_with($route ?? '', 'accountant.invoices') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5-2h.01M9 12h.01M12 20l9-9-9-9-9 9 9 9z"/>
                </svg>
                {{ __('Invoices & Payments') }}
            </a>

            <a href="{{ route('accountant.reports.index') }}"
               class="{{ str_starts_with($route ?? '', 'accountant.reports') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                </svg>
                {{ __('Financial Reports') }}
            </a>

            <a href="{{ route('accountant.expenses.index') }}"
               class="{{ str_starts_with($route ?? '', 'accountant.expenses') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ __('Expenses & Payouts') }}
            </a>
        </nav>

        <div class="shrink-0 border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gold/20 text-sm font-bold text-gold">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name ?? 'Accountant' }}</p>
                    <p class="truncate text-xs text-slate-400">{{ __('Accountant') }}</p>
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
                @yield('page-title', __('Accounts'))
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
