@php $current = app()->getLocale(); @endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.outside="open = false"
            class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium transition
                   {{ $dark ?? false
                       ? 'text-slate-300 hover:bg-white/10 hover:text-white'
                       : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}"
            title="{{ __('Switch Language') }}">
        {{-- Globe icon --}}
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="uppercase font-semibold tracking-wide text-xs">{{ $current }}</span>
        <svg class="h-3 w-3 shrink-0 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-1 w-40 origin-top-right rounded-xl bg-white py-1 shadow-lg ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700 z-50">

        <a href="{{ route('language.switch', 'en') }}"
           class="flex items-center gap-2 px-4 py-2 text-sm transition
                  {{ $current === 'en'
                      ? 'font-semibold text-navy dark:text-navy-light bg-slate-50 dark:bg-slate-700/50'
                      : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700' }}">
            <span class="text-base">🇬🇧</span>
            English
            @if($current === 'en')
                <svg class="ml-auto h-3.5 w-3.5 text-navy dark:text-navy-light" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            @endif
        </a>

        <a href="{{ route('language.switch', 'sw') }}"
           class="flex items-center gap-2 px-4 py-2 text-sm transition
                  {{ $current === 'sw'
                      ? 'font-semibold text-navy dark:text-navy-light bg-slate-50 dark:bg-slate-700/50'
                      : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-700' }}">
            <span class="text-base">🇹🇿</span>
            Kiswahili
            @if($current === 'sw')
                <svg class="ml-auto h-3.5 w-3.5 text-navy dark:text-navy-light" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            @endif
        </a>
    </div>
</div>
