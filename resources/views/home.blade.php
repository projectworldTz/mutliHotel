@extends('layouts.app')
@section('title', __('Hotel Management Platform'))

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@700;800;900&display=swap" rel="stylesheet">
@endpush

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────────────────────── --}}
<section id="hero-section"
         class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-navy to-slate-800 text-white">

    {{-- Floating orb blobs (animated by public-animations.js) --}}
    <div id="hero-orbs" aria-hidden="true">
        <div class="hero-orb w-72 h-72 bg-blue-500   top-[-60px]  left-[-40px]"></div>
        <div class="hero-orb w-96 h-96 bg-gold       top-[60px]   right-[-80px]"></div>
        <div class="hero-orb w-56 h-56 bg-indigo-500 bottom-[20px] left-[30%]"></div>
    </div>

    <div class="hero-content relative mx-auto max-w-7xl px-6 py-28 lg:py-40 text-center">
        <span class="inline-block rounded-full bg-white/10 px-4 py-1.5 text-sm font-medium tracking-wide mb-6"
              data-reveal data-reveal-delay="0">
            {{ __('Multi-Hotel Management Platform') }}
        </span>
        <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl leading-tight"
            data-reveal data-reveal-delay="120">
            {{ __('Run your hotel smarter.') }}<br class="hidden sm:block">
            {{ __('All in one place.') }}
        </h1>
        <p class="mt-6 max-w-2xl mx-auto text-lg text-slate-300"
           data-reveal data-reveal-delay="240">
            {{ __('A complete management system for hotel owners — bookings, rooms, staff, payments and reports under one roof.') }}
        </p>
        <div class="mt-10 flex flex-wrap justify-center gap-4"
             data-reveal data-reveal-delay="360">
            @guest
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-base font-semibold text-navy shadow-lg hover:bg-slate-100 hover:scale-105 active:scale-95 transition-all duration-200">
                    {{ __('Get started free') }}
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/30 px-7 py-3.5 text-base font-semibold hover:bg-white/10 hover:scale-105 active:scale-95 transition-all duration-200">
                    {{ __('Sign in') }}
                </a>
            @else
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-7 py-3.5 text-base font-semibold text-navy shadow-lg hover:bg-slate-100 hover:scale-105 active:scale-95 transition-all duration-200">
                    {{ __('Go to dashboard') }} &rarr;
                </a>
            @endguest
        </div>

        {{-- Scroll indicator --}}
        <div class="mt-16 flex justify-center" data-reveal data-reveal-delay="500">
            @if($demoCredentials && ($demoCredentials['owner_email'] || $demoCredentials['superadmin_email'] || $demoCredentials['hotel_url']))
                <a href="#demo-credentials" id="scroll-to-demo"
                   class="flex flex-col items-center gap-2 text-gold-light hover:text-white transition-colors duration-200">
                    <span id="scroll-to-demo-text"
                          class="font-demo text-lg sm:text-xl font-extrabold uppercase tracking-wide text-center drop-shadow-[0_1px_6px_rgba(201,162,39,0.55)]">
                        {{ __('Click here to get demo credentials') }}
                    </span>
                    <svg class="h-7 w-7 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </a>
            @else
                <div class="flex flex-col items-center gap-1.5 text-white/50 text-xs">
                    <span>{{ __('Scroll to explore') }}</span>
                    <svg class="h-5 w-5 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ── Feature cards ────────────────────────────────────────────────────────── --}}
<section class="bg-white dark:bg-slate-900 py-20">
    <div class="mx-auto max-w-7xl px-6">
        <div class="text-center mb-14" data-reveal>
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">
                {{ __('Everything you need to run a hotel') }}
            </h2>
            <p class="mt-3 text-slate-500 dark:text-slate-400 max-w-xl mx-auto">
                {{ __('Designed for hotel owners and their teams — manage every aspect from one dashboard.') }}
            </p>
        </div>

        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3" data-stagger="80">
            @foreach([
                ['icon' => 'calendar',        'color' => 'text-navy bg-navy/10 dark:bg-navy/20',         'title' => __('Booking Management'),  'desc' => __('Handle reservations end-to-end — availability checks to check-out and invoicing.')],
                ['icon' => 'building-office', 'color' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20', 'title' => __('Room & Rate Control'), 'desc' => __('Configure room types, seasonal pricing and availability calendars with ease.')],
                ['icon' => 'users',           'color' => 'text-blue-600 bg-blue-50 dark:bg-blue-900/20', 'title' => __('Staff & Roles'),       'desc' => __('Add receptionists, managers and cashiers scoped strictly to your hotel.')],
                ['icon' => 'phone',           'color' => 'text-amber-600 bg-amber-50 dark:bg-amber-900/20','title' => __('Mobile Money'),        'desc' => __('Accept Airtel Money, M-Pesa, Halotel and Mix by Yas. Every transaction tracked.')],
                ['icon' => 'chart-bar',       'color' => 'text-purple-600 bg-purple-50 dark:bg-purple-900/20','title' => __('Reports & Revenue'), 'desc' => __('Monthly revenue charts, occupancy rates and booking summaries at a glance.')],
                ['icon' => 'sparkles',        'color' => 'text-rose-500 bg-rose-50 dark:bg-rose-900/20', 'title' => __('Premium Features'),   'desc' => __('AI concierge, housekeeping management, inventory tracking and more — on demand.')],
            ] as $f)
            <div class="rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-6
                        hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-default"
                 data-tilt>
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl {{ $f['color'] }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                        @if($f['icon'] === 'calendar')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        @elseif($f['icon'] === 'building-office')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                        @elseif($f['icon'] === 'users')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                        @elseif($f['icon'] === 'phone')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        @elseif($f['icon'] === 'chart-bar')
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        @endif
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">{{ $f['title'] }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Stats row ────────────────────────────────────────────────────────────── --}}
<section class="bg-slate-50 dark:bg-slate-800/50 py-14 border-y border-slate-100 dark:border-slate-700">
    <div class="mx-auto max-w-7xl px-6">
        <div class="grid grid-cols-2 gap-8 sm:grid-cols-4 text-center">
            @foreach([
                ['val' => 500,  'suffix' => '+', 'label' => __('Hotels managed')],
                ['val' => 12,   'suffix' => '+', 'label' => __('Countries')],
                ['val' => 50,   'suffix' => 'K+','label' => __('Bookings processed')],
                ['val' => 99.9, 'suffix' => '%', 'label' => __('Uptime SLA')],
            ] as $s)
            <div data-reveal>
                <p class="text-4xl font-extrabold text-navy dark:text-navy-light"
                   data-count="{{ $s['val'] }}" data-count-suffix="{{ $s['suffix'] }}">
                    {{ $s['val'] }}{{ $s['suffix'] }}
                </p>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── Demo credentials ─────────────────────────────────────────────────────── --}}
@if($demoCredentials && ($demoCredentials['owner_email'] || $demoCredentials['superadmin_email'] || $demoCredentials['hotel_url']))
<section id="demo-credentials" class="bg-white dark:bg-slate-900 py-20">
    <div class="mx-auto max-w-5xl px-6">
        <div class="text-center mb-12" data-reveal>
            <h2 class="text-3xl font-bold text-slate-900 dark:text-white">
                {{ __('Try it yourself — no sign-up needed') }}
            </h2>
            <p class="mt-3 text-slate-500 dark:text-slate-400 max-w-xl mx-auto">
                {{ __('Log in with the demo accounts below to explore the platform firsthand.') }}
            </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2" data-stagger="80">
            @if($demoCredentials['owner_email'] && $demoCredentials['owner_password'])
            <div class="rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-6">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">{{ __('Hotel Owner Demo') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('See how an existing hotel manages bookings, rooms and staff.') }}</p>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('Email') }}</dt>
                        <dd class="font-mono text-slate-900 dark:text-white">{{ $demoCredentials['owner_email'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('Password') }}</dt>
                        <dd class="font-mono text-slate-900 dark:text-white">{{ $demoCredentials['owner_password'] }}</dd>
                    </div>
                </dl>
                <a href="{{ route('login') }}"
                   class="mt-5 inline-flex items-center gap-2 rounded-xl bg-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-navy/90 transition-colors duration-200">
                    {{ __('Sign in as owner') }} &rarr;
                </a>
            </div>
            @endif

            @if($demoCredentials['superadmin_email'] && $demoCredentials['superadmin_password'])
            <div class="rounded-2xl border border-slate-100 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-6">
                <h3 class="font-semibold text-slate-900 dark:text-white mb-1">{{ __('Super Admin Demo') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('See the platform-wide dashboard across all hotels.') }}</p>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('Email') }}</dt>
                        <dd class="font-mono text-slate-900 dark:text-white">{{ $demoCredentials['superadmin_email'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 dark:text-slate-400">{{ __('Password') }}</dt>
                        <dd class="font-mono text-slate-900 dark:text-white">{{ $demoCredentials['superadmin_password'] }}</dd>
                    </div>
                </dl>
                <a href="{{ route('login') }}"
                   class="mt-5 inline-flex items-center gap-2 rounded-xl bg-navy px-5 py-2.5 text-sm font-semibold text-white hover:bg-navy/90 transition-colors duration-200">
                    {{ __('Sign in as super admin') }} &rarr;
                </a>
            </div>
            @endif
        </div>

        @if($demoCredentials['hotel_url'])
        <div class="mt-8 flex flex-col items-center gap-2 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 p-6 text-center" data-reveal data-reveal-delay="160">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __('Prefer to just look around first?') }}
            </p>
            <a href="{{ $demoCredentials['hotel_url'] }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 rounded-xl border border-navy px-5 py-2.5 text-sm font-semibold text-navy hover:bg-navy hover:text-white dark:text-white dark:border-white/30 dark:hover:bg-white/10 transition-colors duration-200">
                {{ __('Visit :hotel', ['hotel' => $demoCredentials['hotel_name'] ?: __('Demo Hotel')]) }} &rarr;
            </a>
        </div>
        @endif
    </div>
</section>
@endif

{{-- ── CTA ──────────────────────────────────────────────────────────────────── --}}
<section class="bg-navy py-16 text-center text-white">
    <div class="mx-auto max-w-2xl px-6">
        <h2 class="text-3xl font-bold mb-4" data-reveal>{{ __('Ready to get started?') }}</h2>
        <p class="text-slate-300 mb-8" data-reveal data-reveal-delay="100">
            {{ __('Create your account and have your hotel set up in minutes.') }}
        </p>
        <div data-reveal data-reveal-delay="200">
            @guest
                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-navy hover:bg-slate-100 hover:scale-105 active:scale-95 transition-all duration-200 shadow-lg">
                    {{ __('Create a free account') }}
                </a>
            @else
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-base font-semibold text-navy hover:bg-slate-100 hover:scale-105 active:scale-95 transition-all duration-200 shadow-lg">
                    {{ __('Go to your dashboard') }} &rarr;
                </a>
            @endguest
        </div>
    </div>
</section>

@endsection
