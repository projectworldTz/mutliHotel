@extends('layouts.admin')
@section('title', __('Add Hotel Owner'))
@section('page-title', __('Add Hotel Owner'))

@section('content')

<div class="mb-5">
    <a href="{{ route('admin.users.index') }}" class="btn-ghost btn-sm">← {{ __('Back to Users') }}</a>
</div>

<div class="max-w-lg">
    <div class="card p-6">

        <div class="mb-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Create Hotel Owner Account') }}</h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                {{ __('The owner will receive the hotel-owner role and can immediately log in to register and manage their hotel.') }}
            </p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    {{ __('Full Name') }}
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="form-input w-full @error('name') border-rose-400 @enderror"
                       placeholder="Jane Doe">
                @error('name')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    {{ __('Email Address') }}
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="form-input w-full @error('email') border-rose-400 @enderror"
                       placeholder="owner@theirhotel.com">
                @error('email')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    {{ __('Temporary Password') }}
                </label>
                <input type="password" name="password" required
                       class="form-input w-full @error('password') border-rose-400 @enderror"
                       placeholder="Min. 8 characters">
                @error('password')
                    <p class="mt-1 text-xs text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                    {{ __('Confirm Password') }}
                </label>
                <input type="password" name="password_confirmation" required
                       class="form-input w-full"
                       placeholder="Repeat password">
            </div>

            {{-- Role notice --}}
            <div class="rounded-lg bg-navy/5 border border-navy/10 px-4 py-3 text-sm text-slate-600 dark:bg-white/5 dark:border-white/10 dark:text-slate-400">
                <span class="font-semibold text-navy dark:text-white">Role assigned automatically:</span>
                <span class="ml-1 inline-flex items-center rounded-full bg-navy/10 px-2 py-0.5 text-xs font-medium text-navy dark:bg-white/10 dark:text-white">hotel-owner</span>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="btn-primary">
                    {{ __('Create Hotel Owner') }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-ghost">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
