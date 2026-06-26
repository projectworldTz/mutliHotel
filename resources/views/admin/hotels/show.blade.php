@extends('layouts.admin')
@section('title', $hotel->name)
@section('page-title', $hotel->name)

@section('content')
<div class="mb-4"><a href="{{ route('admin.hotels.index') }}" class="btn-ghost btn-sm">← Back to Hotels</a></div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-5">
        {{-- Main info --}}
        <div class="card p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $hotel->name }}</h2>
                    <p class="text-slate-500 mt-1">{{ $hotel->city }}, {{ $hotel->country }}</p>
                </div>
                <div class="flex gap-2">
                    <span class="badge badge-{{ $hotel->status === 'active' ? 'active' : ($hotel->status === 'suspended' ? 'suspended' : 'pending-hotel') }}">
                        {{ ucfirst($hotel->status) }}
                    </span>
                    @if($hotel->is_featured)
                        <span class="badge badge-confirmed">Featured</span>
                    @endif
                </div>
            </div>
            @if($hotel->description)
                <p class="mt-4 text-sm text-slate-600 dark:text-slate-300">{{ $hotel->description }}</p>
            @endif
        </div>

        {{-- Room types --}}
        @if($hotel->roomTypes->isNotEmpty())
        <div class="card">
            <div class="p-5 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-bold text-slate-900 dark:text-white">Room Types ({{ $hotel->roomTypes->count() }})</h3>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Name</th><th>Beds</th><th>Max Guests</th><th>Base Price</th><th>Rooms</th></tr></thead>
                    <tbody>
                        @foreach($hotel->roomTypes as $rt)
                        <tr class="tr-hover">
                            <td class="font-medium">{{ $rt->name }}</td>
                            <td>{{ $rt->beds_count }}× {{ $rt->bed_type }}</td>
                            <td>{{ $rt->max_guests }}</td>
                            <td>${{ number_format($rt->base_price, 2) }}</td>
                            <td>{{ $rt->rooms_count ?? $rt->rooms->count() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Actions sidebar --}}
    <div class="space-y-4">
        <div class="card p-5 space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-3">Actions</h3>

            @if($hotel->status === 'pending')
            <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}">
                @csrf
                <button type="submit" class="btn-success w-full">Approve Hotel</button>
            </form>
            @endif

            @if($hotel->status !== 'suspended')
            <form method="POST" action="{{ route('admin.hotels.suspend', $hotel) }}">
                @csrf
                <button type="submit" class="btn-danger w-full" onclick="return confirm('Suspend this hotel?')">Suspend</button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}">
                @csrf
                <button type="submit" class="btn-success w-full">Reactivate</button>
            </form>
            @endif

            <form method="POST" action="{{ route('admin.hotels.featured', $hotel) }}">
                @csrf
                <button type="submit" class="btn-outline w-full">
                    {{ $hotel->is_featured ? 'Remove from Featured' : 'Mark as Featured' }}
                </button>
            </form>
        </div>

        <div class="card p-5 text-sm space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">Details</h3>
            <div class="flex justify-between"><span class="text-slate-500">Owner</span><span>{{ $hotel->owner->name ?? 'N/A' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Star Rating</span><span>{{ $hotel->star_rating }}★</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Category</span><span>{{ $hotel->category->name ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Check-in</span><span>{{ $hotel->check_in_time ?? '14:00' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Check-out</span><span>{{ $hotel->check_out_time ?? '11:00' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Listed</span><span>{{ $hotel->created_at->format('d M Y') }}</span></div>
        </div>
    </div>
</div>
@endsection
