@extends('layouts.admin')
@section('title', $user->name)
@section('page-title', 'User Profile')

@section('content')

<div class="mb-5">
    <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm">← Back to Users</a>
</div>

<div class="grid gap-6 lg:grid-cols-3">

    {{-- ── Left: user info card ─────────────────────────────────────────────── --}}
    <div class="space-y-5">

        {{-- Profile --}}
        <div class="card p-6 text-center">
            <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-navy text-2xl font-bold text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $user->name }}</h2>
            <p class="text-sm text-slate-500">{{ $user->email }}</p>
            <div class="mt-2 flex flex-wrap justify-center gap-1">
                @foreach($user->roles as $role)
                <span class="badge badge-pending">{{ $role->name }}</span>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-slate-400">Joined {{ $user->created_at->format('d M Y') }}</p>
        </div>

        {{-- Role management --}}
        <div class="card p-5">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Assign Role</h3>
            <form method="POST" action="{{ route('admin.users.assign-role', $user) }}" class="flex gap-2">
                @csrf
                <select name="role_id" class="form-input flex-1 text-sm">
                    @foreach(\App\Models\Role::all() as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                <button class="btn-primary btn-sm shrink-0">Assign</button>
            </form>
            @if($user->roles->count())
            <div class="mt-3 space-y-1.5">
                @foreach($user->roles as $role)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-700 dark:text-slate-300">{{ $role->name }}</span>
                    <form method="POST" action="{{ route('admin.users.revoke-role', [$user, $role]) }}">
                        @csrf @method('DELETE')
                        <button class="text-xs text-rose-500 hover:underline">Revoke</button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Toggle active --}}
        <div class="card p-5">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 mb-3">Account Status</h3>
            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                @csrf @method('PATCH')
                <button class="w-full {{ ($user->is_active ?? true) ? 'btn-ghost border-rose-300 text-rose-600 hover:bg-rose-50' : 'btn-primary' }} btn-sm">
                    {{ ($user->is_active ?? true) ? 'Deactivate Account' : 'Activate Account' }}
                </button>
            </form>
        </div>
    </div>

    {{-- ── Right: main content ──────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Hotel limit (only relevant for hotel owners) --}}
        @if($user->roles->contains('slug', 'hotel_owner') || $user->ownedHotels->count() > 0)
        <div class="card p-5">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Multi-Property Limit</h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Controls how many hotels this owner can register. Default is 1.
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->ownedHotels->count() }}</p>
                    <p class="text-xs text-slate-400">of {{ $user->max_hotels ?? 1 }} used</p>
                </div>
            </div>

            {{-- Occupancy bar --}}
            @php
                $used  = $user->ownedHotels->count();
                $limit = (int) ($user->max_hotels ?? 1);
                $pct   = $limit > 0 ? min(round(($used / $limit) * 100), 100) : 100;
            @endphp
            <div class="mb-4 w-full bg-slate-200 dark:bg-slate-600 rounded-full h-2">
                <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-rose-500' : ($pct >= 80 ? 'bg-amber-400' : 'bg-emerald-500') }}"
                     style="width:{{ $pct }}%"></div>
            </div>

            <form method="POST" action="{{ route('admin.users.hotel-limit', $user) }}"
                  class="flex items-end gap-3">
                @csrf @method('PATCH')
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Max Hotels Allowed</label>
                    <input type="number" name="max_hotels" min="1" max="99"
                           value="{{ $user->max_hotels ?? 1 }}"
                           class="form-input w-full">
                </div>
                <button class="btn-primary shrink-0">Update Limit</button>
            </form>
            <p class="mt-2 text-xs text-slate-400">
                Set to 1 to restrict the owner to a single hotel. Increase when they have an agreement for multi-property access.
            </p>
        </div>
        @endif

        {{-- Owned hotels --}}
        @if($user->ownedHotels->count())
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Owned Hotels</h3>
            </div>
            <table class="table">
                <thead>
                    <tr><th>Hotel</th><th>City</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($user->ownedHotels as $hotel)
                    <tr class="tr-hover">
                        <td class="font-medium text-slate-900 dark:text-white">{{ $hotel->name }}</td>
                        <td class="text-slate-500">{{ $hotel->city }}</td>
                        <td>
                            @php
                                $sc = ['active'=>'badge-active','pending'=>'badge-pending','suspended'=>'badge-suspended'];
                            @endphp
                            <span class="badge {{ $sc[$hotel->status] ?? 'badge-pending' }}">{{ ucfirst($hotel->status) }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.hotels.show', $hotel) }}" class="btn-ghost btn-sm">View Hub</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Recent bookings --}}
        @if($user->bookings->count())
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">Recent Bookings</h3>
            </div>
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Hotel</th><th>Dates</th><th>Status</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @foreach($user->bookings->take(10) as $booking)
                    <tr class="tr-hover">
                        <td class="font-mono text-xs">{{ $booking->booking_number }}</td>
                        <td>{{ $booking->hotel->name ?? '—' }}</td>
                        <td class="text-xs text-slate-500 whitespace-nowrap">{{ $booking->check_in->format('d M') }} – {{ $booking->check_out->format('d M Y') }}</td>
                        <td><span class="badge badge-{{ $booking->status_badge['color'] }}">{{ $booking->status_badge['label'] }}</span></td>
                        <td class="font-semibold">{{ money($booking->grand_total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>
</div>

@endsection
