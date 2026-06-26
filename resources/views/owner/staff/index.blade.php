@extends('layouts.owner')
@section('title', 'Staff — ' . $hotel->name)
@section('page-title', 'Staff — ' . $hotel->name)

@section('content')
<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">← {{ $hotel->name }}</a>
    <a href="{{ route('owner.hotels.staff.create', $hotel) }}" class="btn-primary btn-sm ml-auto">+ Invite Staff</a>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Position</th><th>Status</th><th>Added</th><th></th></tr>
        </thead>
        <tbody>
            @forelse($staff as $member)
            <tr class="tr-hover">
                <td class="font-medium">{{ $member->user->name }}</td>
                <td class="text-slate-500">{{ $member->user->email }}</td>
                <td class="capitalize">{{ $member->position }}</td>
                <td>
                    <span class="badge {{ $member->active ? 'badge-active' : 'badge-cancelled' }}">
                        {{ $member->active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-slate-500">{{ $member->created_at->format('d M Y') }}</td>
                <td>
                    <div class="flex items-center gap-1">
                        <form method="POST" action="{{ route('owner.hotels.staff.toggle', [$hotel, $member->user]) }}">
                            @csrf
                            <button class="btn-ghost btn-sm">{{ $member->active ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                        <form method="POST" action="{{ route('owner.hotels.staff.destroy', [$hotel, $member->user]) }}"
                              x-data x-on:submit.prevent="if(confirm('Remove this staff member?')) $el.submit()">
                            @csrf @method('DELETE')
                            <button class="btn-danger btn-sm">Remove</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-slate-500">No staff assigned yet. <a href="{{ route('owner.hotels.staff.create', $hotel) }}" class="text-navy underline">Invite someone</a>.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
