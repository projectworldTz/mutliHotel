<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data
      x-bind:class="{ 'dark': $store.theme.dark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Admin')) — {{ config('app.name') }} {{ __('Admin') }}</title>
    <script>if (localStorage.getItem('theme') === 'dark') document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-slate-100 dark:bg-slate-950" x-data="{ sidebarOpen: false }">
<div class="flex min-h-screen">

    {{-- ── Sidebar ── --}}
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-navy-dark transition-transform duration-300 ease-in-out
                  lg:translate-x-0 lg:static lg:flex"
           :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }">

        {{-- Brand --}}
        <div class="flex h-16 shrink-0 items-center gap-2 border-b border-white/10 px-5">
            <svg class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-base font-bold text-white">{{ config('app.name') }}</span>
            <span class="ml-auto rounded-full bg-gold/20 px-2 py-0.5 text-[10px] font-semibold uppercase text-gold">{{ __('Admin') }}</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
            @php
                $route = Route::currentRouteName();
                $isAdmin = fn($prefix) => str_starts_with($route ?? '', 'admin.' . $prefix);
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="{{ str_starts_with($route ?? '', 'admin.dashboard') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                {{ __('Dashboard') }}
            </a>

            @php $pendingHotels = \App\Models\Hotel::where('status', 'pending')->count(); @endphp
            <a href="{{ route('admin.hotels.index') }}"
               class="{{ $isAdmin('hotels') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="flex-1">{{ __('Hotels') }}</span>
                @if($pendingHotels > 0)
                <span class="ml-auto inline-flex items-center justify-center h-5 w-5 rounded-full bg-amber-500 text-[10px] font-bold text-white">
                    {{ $pendingHotels > 9 ? '9+' : $pendingHotels }}
                </span>
                @endif
            </a>

            <a href="{{ route('admin.bookings.index') }}"
               class="{{ $isAdmin('bookings') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ __('Bookings') }}
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="{{ $isAdmin('users') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{ __('Users') }}
            </a>

            <div class="my-2 border-t border-white/10"></div>

            <a href="{{ route('admin.reports.revenue') }}"
               class="{{ $isAdmin('reports') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                {{ __('Reports') }}
            </a>

            <a href="{{ route('admin.audit-logs.index') }}"
               class="{{ $isAdmin('audit-logs') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                {{ __('Audit Logs') }}
            </a>

            @php $openErrorLogs = \App\Models\ErrorLog::where('status', 'open')->count(); @endphp
            <a href="{{ route('admin.error-logs.index') }}"
               class="{{ $isAdmin('error-logs') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <span class="flex-1">{{ __('Error Logs') }}</span>
                @if($openErrorLogs > 0)
                <span class="ml-auto inline-flex items-center justify-center h-5 w-5 rounded-full bg-rose-500 text-[10px] font-bold text-white">
                    {{ $openErrorLogs > 9 ? '9+' : $openErrorLogs }}
                </span>
                @endif
            </a>

            {{-- Feature Requests with notification badge --}}
            @php $pendingFeatureReqs = \App\Models\FeatureRequest::pending()->count(); @endphp
            <a href="{{ route('admin.feature-requests.index') }}"
               class="{{ $isAdmin('feature-requests') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="flex-1">{{ __('Feature Requests') }}</span>
                @if($pendingFeatureReqs > 0)
                <span class="ml-auto inline-flex items-center justify-center h-5 w-5 rounded-full bg-amber-500 text-[10px] font-bold text-white">
                    {{ $pendingFeatureReqs > 9 ? '9+' : $pendingFeatureReqs }}
                </span>
                @endif
            </a>

            <a href="{{ route('admin.settings.index') }}"
               class="{{ $isAdmin('settings') ? 'nav-link-active' : 'nav-link' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ __('Settings') }}
            </a>
        </nav>

        {{-- User info at bottom --}}
        <div class="shrink-0 border-t border-white/10 p-4">
            <div class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gold/20 text-sm font-bold text-gold">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="truncate text-xs text-slate-400">{{ __('Super Admin') }}</p>
                </div>
                <a href="{{ route('home') }}" title="{{ __('View Site') }}" class="text-slate-400 hover:text-white transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        </div>
    </aside>

    {{-- Sidebar overlay for mobile --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    {{-- ── Main area ── --}}
    <div class="flex min-w-0 flex-1 flex-col">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white px-4 dark:border-slate-700 dark:bg-slate-900 sm:px-6">
            {{-- Mobile sidebar toggle --}}
            <button @click="sidebarOpen = true"
                    class="btn-ghost p-2 rounded-lg lg:hidden">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <h1 class="flex-1 text-base font-semibold text-slate-900 dark:text-white">
                @yield('page-title', __('Dashboard'))
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

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-ghost btn-sm">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="hidden sm:inline">{{ __('Logout') }}</span>
                </button>
            </form>
        </header>

        {{-- Impersonation banner --}}
        @if(session()->has('impersonating_original_id'))
        <div class="bg-amber-500 dark:bg-amber-600 px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2 text-sm font-semibold text-white">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                {{ __('You are impersonating') }} <strong class="underline">{{ auth()->user()->name }}</strong>.
                {{ __('All actions you take are real and will be audit-logged.') }}
            </div>
            <form method="DELETE" action="{{ route('admin.impersonate.stop') }}"
                  onsubmit="this.method='POST'; this.querySelector('[name=_method]').value='DELETE';">
                @csrf
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit"
                        class="rounded-lg bg-white/20 hover:bg-white/30 px-3 py-1 text-xs font-bold text-white transition">
                    {{ __('Stop Impersonating') }} ×
                </button>
            </form>
        </div>
        @endif

        {{-- Pending hotel approvals banner --}}
        @if(($pendingHotels ?? 0) > 0 && ! $isAdmin('hotels'))
        <div class="bg-sky-50 dark:bg-sky-900/20 border-b border-sky-200 dark:border-sky-800 px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-2 text-sm font-medium text-sky-700 dark:text-sky-300">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ trans_choice('{1} :count hotel is waiting for approval.|[2,*] :count hotels are waiting for approval.', $pendingHotels, ['count' => $pendingHotels]) }}
            </div>
            <a href="{{ route('admin.hotels.index', ['status' => 'pending']) }}" class="btn-primary btn-sm">{{ __('Review Now') }}</a>
        </div>
        @endif

        {{-- Flash --}}
        <div class="px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="mt-4 alert-success">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
                     class="mt-4 rounded-xl border border-sky-200 dark:border-sky-700 bg-sky-50 dark:bg-sky-900/20 px-4 py-3 text-sm text-sky-700 dark:text-sky-300">
                    {{ session('info') }}
                </div>
            @endif
            @if(session('error') || $errors->any())
                <div class="mt-4 alert-error">
                    @if(session('error')) {{ session('error') }} @endif
                    @foreach($errors->all() as $e) <p>{{ $e }}</p> @endforeach
                </div>
            @endif
        </div>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
