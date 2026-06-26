@extends('layouts.owner')
@section('title', 'My Hotels')
@section('page-title', 'My Hotels')

@section('content')
<div class="mb-5 flex justify-end">
    <a href="{{ route('owner.hotels.create') }}" class="btn-primary">+ Add Hotel</a>
</div>

@if($hotels->isEmpty())
    <div class="card flex flex-col items-center justify-center py-20 text-center">
        <svg class="h-14 w-14 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">No hotels listed</h3>
        <p class="mt-1 text-sm text-slate-500">Add your first hotel to start accepting bookings.</p>
        <a href="{{ route('owner.hotels.create') }}" class="btn-primary mt-5">List Your Hotel</a>
    </div>
@else
<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr><th>Hotel</th><th>City</th><th>Stars</th><th>Rooms</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            @foreach($hotels as $hotel)
            <tr class="tr-hover">
                <td class="font-medium text-slate-900 dark:text-white">{{ $hotel->name }}</td>
                <td>{{ $hotel->city }}</td>
                <td><div class="flex text-gold text-xs">@for($i=0;$i<$hotel->star_rating;$i++) ★ @endfor</div></td>
                <td>{{ $hotel->rooms_count ?? $hotel->rooms->count() }}</td>
                <td><span class="badge badge-{{ $hotel->status === 'active' ? 'active' : ($hotel->status === 'suspended' ? 'suspended' : 'pending-hotel') }}">{{ ucfirst($hotel->status) }}</span></td>
                <td>
                    <div class="flex gap-1">
                        <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">View</a>
                        <a href="{{ route('owner.hotels.edit', $hotel) }}" class="btn-outline btn-sm">Edit</a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection
