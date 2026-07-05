@extends('layouts.admin')
@section('title', __('Users'))
@section('page-title', __('Users'))

@section('content')
<div class="mb-5 flex items-center justify-between gap-4 flex-wrap">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               class="form-input w-full sm:w-56 py-2 text-sm" placeholder="{{ __('Name or email…') }}">
        <select name="role" class="form-select py-2 text-sm w-auto">
            <option value="">{{ __('All Roles') }}</option>
            <option value="super-admin"  {{ request('role') === 'super-admin'  ? 'selected' : '' }}>{{ __('Super Admin') }}</option>
            <option value="hotel-owner"  {{ request('role') === 'hotel-owner'  ? 'selected' : '' }}>{{ __('Hotel Owner') }}</option>
            <option value="receptionist" {{ request('role') === 'receptionist' ? 'selected' : '' }}>{{ __('Receptionist') }}</option>
            <option value="manager"      {{ request('role') === 'manager'      ? 'selected' : '' }}>{{ __('Manager') }}</option>
            <option value="cashier"      {{ request('role') === 'cashier'      ? 'selected' : '' }}>{{ __('Cashier') }}</option>
            <option value="customer"     {{ request('role') === 'customer'     ? 'selected' : '' }}>{{ __('Customer') }}</option>
        </select>
        <select name="hotel_id" class="form-select py-2 text-sm w-auto">
            <option value="">{{ __('All Hotels') }}</option>
            @foreach($hotels as $h)
            <option value="{{ $h->id }}" {{ (string) request('hotel_id') === (string) $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary btn-sm">{{ __('Filter') }}</button>
        <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm">{{ __('Reset') }}</a>
    </form>
    <a href="{{ route('admin.users.create') }}" class="btn-primary btn-sm whitespace-nowrap">
        + {{ __('Add Hotel Owner') }}
    </a>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Role') }}</th>
                <th>{{ __('Hotel') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Joined') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr class="tr-hover">
                <td class="font-medium text-slate-900 dark:text-white">{{ $user->name }}</td>
                <td class="text-slate-500">{{ $user->email }}</td>
                <td>
                    @foreach($user->roles as $role)
                        <span class="badge badge-pending mr-1">{{ $role->name }}</span>
                    @endforeach
                </td>
                <td class="text-sm">
                    @if($user->ownedHotels->isNotEmpty())
                        @foreach($user->ownedHotels as $h)
                            <a href="{{ route('admin.hotels.show', $h) }}" class="text-navy dark:text-amber-400 hover:underline block">{{ $h->name }}</a>
                        @endforeach
                    @elseif($user->staffAssignments->isNotEmpty())
                        @foreach($user->staffAssignments as $assignment)
                            @if($assignment->hotel)
                            <a href="{{ route('admin.hotels.show', $assignment->hotel) }}" class="text-navy dark:text-amber-400 hover:underline block">
                                {{ $assignment->hotel->name }}
                            </a>
                            @endif
                        @endforeach
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                <td>
                    @if($user->is_active ?? true)
                        <span class="badge badge-active">{{ __('Active') }}</span>
                    @else
                        <span class="badge badge-suspended">{{ __('Inactive') }}</span>
                    @endif
                </td>
                <td class="text-sm text-slate-500 whitespace-nowrap">{{ $user->created_at->format('d M Y') }}</td>
                <td><a href="{{ route('admin.users.show', $user) }}" class="btn-ghost btn-sm">{{ __('Manage') }}</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-10 text-slate-500">{{ __('No users found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->withQueryString()->links() }}</div>
@endsection
