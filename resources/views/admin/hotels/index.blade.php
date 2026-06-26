@extends('layouts.admin')
@section('title', 'Hotels')
@section('page-title', 'Hotels')

@section('content')
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <form method="GET" action="{{ route('admin.hotels.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               class="form-input w-48 py-2 text-sm" placeholder="Search hotels…">
        <select name="status" class="form-select py-2 text-sm w-auto">
            <option value="">All Status</option>
            @foreach(['pending', 'active', 'suspended'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.hotels.index') }}" class="btn-ghost btn-sm">Reset</a>
    </form>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Hotel</th>
                <th>Owner</th>
                <th>City</th>
                <th>Stars</th>
                <th>Status</th>
                <th>Featured</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($hotels as $hotel)
            <tr class="tr-hover">
                <td>
                    <p class="font-medium text-slate-900 dark:text-white">{{ $hotel->name }}</p>
                    <p class="text-xs text-slate-500">{{ $hotel->category->name ?? '' }}</p>
                </td>
                <td>{{ $hotel->owner->name ?? 'N/A' }}</td>
                <td>{{ $hotel->city }}</td>
                <td>
                    <div class="flex text-gold text-xs">
                        @for($i = 0; $i < $hotel->star_rating; $i++) ★ @endfor
                    </div>
                </td>
                <td><span class="badge badge-{{ $hotel->status === 'active' ? 'active' : ($hotel->status === 'suspended' ? 'suspended' : 'pending-hotel') }}">{{ ucfirst($hotel->status) }}</span></td>
                <td>
                    @if($hotel->is_featured)
                        <span class="badge badge-confirmed">Yes</span>
                    @else
                        <span class="text-xs text-slate-400">—</span>
                    @endif
                </td>
                <td class="text-sm text-slate-500">{{ $hotel->created_at->format('d M Y') }}</td>
                <td>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.hotels.show', $hotel) }}" class="btn-ghost btn-sm">View</a>
                        @if($hotel->status === 'pending')
                        <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}">
                            @csrf
                            <button type="submit" class="btn-success btn-sm">Approve</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-10 text-slate-500">No hotels found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $hotels->withQueryString()->links() }}</div>
@endsection
