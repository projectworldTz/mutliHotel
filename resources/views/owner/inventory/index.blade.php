@extends('layouts.owner')
@section('title', 'Inventory — ' . $hotel->name)
@section('page-title', 'Inventory & Assets')

@section('content')

{{-- ── Header ─────────────────────────────────────────────────────────────── --}}
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('owner.hotels.show', $hotel) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
            ← {{ $hotel->name }}
        </a>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Asset Register</h2>
    </div>
    <button x-data @click="$dispatch('open-add-asset')"
            class="btn-primary flex items-center gap-2">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Asset
    </button>
</div>

{{-- ── Summary Cards ────────────────────────────────────────────────────────── --}}
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 mb-6">
    @foreach([
        ['Total Assets',     $summary['total'],       'text-slate-700 dark:text-white',           'bg-slate-100 dark:bg-slate-700'],
        ['Active',           $summary['active'],      'text-emerald-600 dark:text-emerald-400',   'bg-emerald-50 dark:bg-emerald-900/20'],
        ['Maintenance',      $summary['maintenance'], 'text-amber-600 dark:text-amber-400',       'bg-amber-50 dark:bg-amber-900/20'],
        ['Damaged',          $summary['damaged'],     'text-rose-600 dark:text-rose-400',         'bg-rose-50 dark:bg-rose-900/20'],
        ['Total Value',      money($summary['total_value']), 'text-purple-600 dark:text-purple-400', 'bg-purple-50 dark:bg-purple-900/20'],
    ] as [$label, $val, $tc, $bg])
    <div class="rounded-2xl {{ $bg }} border border-white/60 dark:border-slate-700 p-4">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ $label }}</p>
        <p class="text-2xl font-bold {{ $tc }}">{{ $val }}</p>
    </div>
    @endforeach
</div>

{{-- ── Filters + Table ─────────────────────────────────────────────────────── --}}
<div class="grid gap-6 lg:grid-cols-4">

    {{-- Sidebar: category breakdown ─────────────────────────────────────────── --}}
    <div class="lg:col-span-1">
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-4">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">By Category</h3>
            @php $totalAssets = max($summary['total'], 1); @endphp
            @forelse($categoryBreakdown as $row)
            @php
                $colorMap = ['amber'=>'bg-amber-400','blue'=>'bg-blue-400','purple'=>'bg-purple-400','orange'=>'bg-orange-400','emerald'=>'bg-emerald-400','cyan'=>'bg-cyan-400','rose'=>'bg-rose-400','slate'=>'bg-slate-400','gray'=>'bg-gray-400'];
                $bar = $colorMap[$row->category->color ?? 'slate'] ?? 'bg-slate-400';
            @endphp
            <a href="{{ route('owner.inventory.index', ['hotel' => $hotel, 'category' => $row->asset_category_id]) }}"
               class="block mb-2.5 group">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-slate-600 dark:text-slate-300 group-hover:text-slate-900 dark:group-hover:text-white transition">
                        {{ $row->category->name }}
                    </span>
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $row->count }}</span>
                </div>
                <div class="h-1.5 w-full bg-slate-200 dark:bg-slate-600 rounded-full">
                    <div class="{{ $bar }} h-1.5 rounded-full"
                         style="width:{{ round(($row->count / $totalAssets) * 100) }}%"></div>
                </div>
            </a>
            @empty
            <p class="text-xs text-slate-400">No assets yet.</p>
            @endforelse

            @if(request()->hasAny(['category','status','condition','search']))
            <a href="{{ route('owner.inventory.index', $hotel) }}" class="mt-3 block text-xs text-center text-slate-400 hover:text-slate-600 transition">Clear filters</a>
            @endif
        </div>
    </div>

    {{-- Main: filters + asset table ─────────────────────────────────────────── --}}
    <div class="lg:col-span-3 space-y-4">

        {{-- Filter bar --}}
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Name, code, location…"
                   class="form-input w-full sm:w-48 text-sm">
            <select name="category" class="form-input w-auto text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="condition" class="form-input w-auto text-sm">
                <option value="">Any Condition</option>
                @foreach(['excellent'=>'Excellent','good'=>'Good','fair'=>'Fair','poor'=>'Poor','damaged'=>'Damaged'] as $v => $l)
                <option value="{{ $v }}" @selected(request('condition') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <select name="status" class="form-input w-auto text-sm">
                <option value="">Any Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="under_maintenance" @selected(request('status') === 'under_maintenance')>Maintenance</option>
                <option value="disposed" @selected(request('status') === 'disposed')>Disposed</option>
            </select>
            <button type="submit" class="btn-primary btn-sm">Filter</button>
        </form>

        {{-- Asset table --}}
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Asset</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Qty</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th>Value</th>
                            <th>Warranty</th>
                            <th class="w-20"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        @php
                            $condColors = ['excellent'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400','good'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400','fair'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400','poor'=>'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400','damaged'=>'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'];
                            $statColors = ['active'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400','under_maintenance'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400','disposed'=>'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'];
                            $warrantyStatus = $asset->warranty_status;
                        @endphp
                        <tr class="tr-hover" x-data="{ editOpen: false }">
                            <td class="font-mono text-xs text-slate-500">{{ $asset->asset_code }}</td>
                            <td>
                                <p class="font-medium text-slate-900 dark:text-white">{{ $asset->name }}</p>
                                @if($asset->description)
                                <p class="text-xs text-slate-400 truncate max-w-[160px]" title="{{ $asset->description }}">{{ $asset->description }}</p>
                                @endif
                            </td>
                            <td class="text-sm text-slate-600 dark:text-slate-300">{{ $asset->category->name }}</td>
                            <td class="text-sm text-slate-500">{{ $asset->location ?? '—' }}</td>
                            <td class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $asset->quantity }}</td>
                            <td>
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $condColors[$asset->condition] ?? '' }}">
                                    {{ ucfirst($asset->condition) }}
                                </span>
                            </td>
                            <td>
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $statColors[$asset->status] ?? '' }}">
                                    {{ ucwords(str_replace('_', ' ', $asset->status)) }}
                                </span>
                            </td>
                            <td class="text-sm text-slate-600 dark:text-slate-300">
                                {{ $asset->purchase_price ? money($asset->purchase_price * $asset->quantity) : '—' }}
                            </td>
                            <td class="text-xs">
                                @if($asset->warranty_expires_at)
                                    @if($warrantyStatus === 'expired')
                                        <span class="text-rose-500">Expired</span>
                                    @elseif($warrantyStatus === 'expiring')
                                        <span class="text-amber-500">Expires {{ $asset->warranty_expires_at->format('d M Y') }}</span>
                                    @else
                                        <span class="text-emerald-500">{{ $asset->warranty_expires_at->format('d M Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    {{-- Edit button --}}
                                    <button @click="editOpen = true"
                                            class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    {{-- Delete button --}}
                                    <form method="POST" action="{{ route('owner.inventory.destroy', [$hotel, $asset]) }}"
                                          onsubmit="return confirm('Delete asset {{ addslashes($asset->name) }}?')">
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
                                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Asset</h3>
                                            <button @click="editOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        @include('owner.inventory._form', ['asset' => $asset, 'categories' => $categories, 'hotel' => $hotel, 'action' => route('owner.inventory.update', [$hotel, $asset]), 'method' => 'PUT'])
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-14 text-center">
                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                <p class="text-slate-500 font-medium">No assets found.</p>
                                <p class="text-slate-400 text-sm mt-1">Add your first asset using the button above.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($assets->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $assets->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ── Add Asset Modal ──────────────────────────────────────────────────────── --}}
<div x-data="{ open: false }"
     x-on:open-add-asset.window="open = true"
     x-show="open"
     x-trap="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add New Asset</h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @include('owner.inventory._form', ['asset' => null, 'categories' => $categories, 'hotel' => $hotel, 'action' => route('owner.inventory.store', $hotel), 'method' => 'POST'])
    </div>
</div>

@endsection
