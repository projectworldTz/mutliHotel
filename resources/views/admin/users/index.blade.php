@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="mb-5">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               class="form-input w-56 py-2 text-sm" placeholder="Name or email…">
        <select name="role" class="form-select py-2 text-sm w-auto">
            <option value="">All Roles</option>
            <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            <option value="hotel_owner" {{ request('role') === 'hotel_owner' ? 'selected' : '' }}>Hotel Owner</option>
            <option value="customer"    {{ request('role') === 'customer'    ? 'selected' : '' }}>Customer</option>
        </select>
        <button type="submit" class="btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm">Reset</a>
    </form>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
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
                <td>
                    @if($user->is_active ?? true)
                        <span class="badge badge-active">Active</span>
                    @else
                        <span class="badge badge-suspended">Inactive</span>
                    @endif
                </td>
                <td class="text-sm text-slate-500 whitespace-nowrap">{{ $user->created_at->format('d M Y') }}</td>
                <td><a href="{{ route('admin.users.show', $user) }}" class="btn-ghost btn-sm">Manage</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-10 text-slate-500">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->withQueryString()->links() }}</div>
@endsection
