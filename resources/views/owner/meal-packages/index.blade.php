@extends('layouts.owner')
@section('title', 'Meal Packages — ' . $hotel->name)
@section('page-title', 'Meal Packages & Add-ons')

@section('content')

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('owner.hotels.show', $hotel) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            ← {{ $hotel->name }}
        </a>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Meal Packages & Add-ons</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Board plans (Bed & Breakfast, Half Board, Full Board) and one-off add-ons guests can select at checkout.</p>
    </div>
    <button x-data @click="$dispatch('open-add-meal-package')" class="btn-primary flex items-center gap-2">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Meal Package
    </button>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Pricing</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="w-20"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($mealPackages as $package)
                @php $pricingLabels = ['per_night' => 'Per Night', 'per_stay' => 'Per Stay (flat)', 'per_guest' => 'Per Guest']; @endphp
                <tr class="tr-hover" x-data="{ editOpen: false }">
                    <td>
                        <p class="font-medium text-slate-900 dark:text-white">{{ $package->name }}</p>
                        @if($package->description)
                        <p class="text-xs text-slate-400 truncate max-w-[220px]" title="{{ $package->description }}">{{ $package->description }}</p>
                        @endif
                    </td>
                    <td class="text-sm text-slate-600 dark:text-slate-300">{{ $pricingLabels[$package->pricing_type] ?? $package->pricing_type }}</td>
                    <td class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ money($package->price) }}</td>
                    <td>
                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $package->active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' }}">
                            {{ $package->active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-center gap-1">
                            <button @click="editOpen = true"
                                    class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <form method="POST" action="{{ route('owner.meal-packages.destroy', [$hotel, $package]) }}"
                                  onsubmit="return confirm('Delete meal package {{ addslashes($package->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>

                        {{-- Inline edit panel --}}
                        <div x-show="editOpen" x-trap="editOpen" @click.outside="editOpen = false"
                             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                            <div class="absolute inset-0 bg-black/50" @click="editOpen = false"></div>
                            <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                                <div class="flex items-center justify-between mb-5">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Meal Package</h3>
                                    <button @click="editOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                @include('owner.meal-packages._form', ['package' => $package, 'hotel' => $hotel, 'action' => route('owner.meal-packages.update', [$hotel, $package]), 'method' => 'PUT'])
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-14 text-center">
                        <p class="text-slate-500 font-medium">No meal packages yet.</p>
                        <p class="text-slate-400 text-sm mt-1">Add board plans or add-on packages guests can choose during checkout.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Add Meal Package Modal ───────────────────────────────────────────────── --}}
<div x-data="{ open: false }"
     x-on:open-add-meal-package.window="open = true"
     x-show="open"
     x-trap="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add Meal Package</h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @include('owner.meal-packages._form', ['package' => null, 'hotel' => $hotel, 'action' => route('owner.meal-packages.store', $hotel), 'method' => 'POST'])
    </div>
</div>

@endsection
