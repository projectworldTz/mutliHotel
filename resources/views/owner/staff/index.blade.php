@extends('layouts.owner')
@section('title', __('Staff') . ' — ' . $hotel->name)
@section('page-title', __('Staff') . ' — ' . $hotel->name)

@section('content')

@if(session('temp_password'))
<div class="mb-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-5">
    <h3 class="font-bold text-emerald-900 dark:text-emerald-100">{{ __('Staff account created') }}</h3>
    <p class="mt-1 text-sm text-emerald-800 dark:text-emerald-200">
        {{ __('Share these sign-in details with the new staff member. This password is shown only once — they can change it after logging in from Account Settings.') }}
    </p>
    <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-1 font-mono text-sm">
        <span class="text-emerald-900 dark:text-emerald-100"><span class="text-emerald-600 dark:text-emerald-400 font-sans">{{ __('Email') }}:</span> {{ session('temp_password_email') }}</span>
        <span class="text-emerald-900 dark:text-emerald-100"><span class="text-emerald-600 dark:text-emerald-400 font-sans">{{ __('Temporary Password') }}:</span> {{ session('temp_password') }}</span>
    </div>
</div>
@endif

<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">← {{ $hotel->name }}</a>
    <a href="{{ route('owner.hotels.staff.create', $hotel) }}" class="btn-primary btn-sm ml-auto">+ {{ __('Invite Staff') }}</a>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Email') }}</th>
                <th>{{ __('Position') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Added') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $member)
            <tr class="tr-hover">
                <td class="font-medium">{{ $member->user->name }}</td>
                <td class="text-slate-500">{{ $member->user->email }}</td>
                <td class="capitalize">{{ $member->position }}</td>
                <td>
                    <span class="badge {{ $member->active ? 'badge-active' : 'badge-cancelled' }}">
                        {{ $member->active ? __('Active') : __('Inactive') }}
                    </span>
                </td>
                <td class="text-slate-500">{{ $member->created_at->format('d M Y') }}</td>
                <td>
                    <div class="flex items-center gap-1">
                        <form method="POST" action="{{ route('owner.hotels.staff.toggle', [$hotel, $member->user]) }}">
                            @csrf
                            <button class="btn-ghost btn-sm">{{ $member->active ? __('Deactivate') : __('Activate') }}</button>
                        </form>
                        <form method="POST" action="{{ route('owner.hotels.staff.destroy', [$hotel, $member->user]) }}"
                              x-data x-on:submit.prevent="if(confirm('{{ __('Remove this staff member?') }}')) $el.submit()">
                            @csrf @method('DELETE')
                            <button class="btn-danger btn-sm">{{ __('Remove') }}</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="py-10 text-center text-slate-500">{{ __('No staff assigned yet.') }} <a href="{{ route('owner.hotels.staff.create', $hotel) }}" class="text-navy underline">{{ __('Invite someone') }}</a>.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
