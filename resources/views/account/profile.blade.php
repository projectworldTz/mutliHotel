@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="page-header">
        <h1 class="page-title">Profile Settings</h1>
    </div>

    <div class="space-y-6">
        {{-- Update profile --}}
        <div class="card p-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">Personal Information</h2>
            <form method="POST" action="{{ route('account.profile.update') }}">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                               class="form-input @error('name') border-rose-500 @enderror" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                               class="form-input @error('email') border-rose-500 @enderror" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Phone <span class="font-normal text-slate-400">(optional)</span></label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                               class="form-input" placeholder="+1 555 000 0000">
                    </div>
                </div>
                <button type="submit" class="btn-primary mt-5">Save Changes</button>
            </form>
        </div>

        {{-- Change password --}}
        <div class="card p-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">Change Password</h2>
            <form method="POST" action="{{ route('account.password.update') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password"
                               class="form-input @error('current_password') border-rose-500 @enderror"
                               placeholder="••••••••">
                        @error('current_password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">New Password</label>
                        <input type="password" name="password"
                               class="form-input @error('password') border-rose-500 @enderror"
                               placeholder="Min. 8 characters">
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                               class="form-input" placeholder="Repeat new password">
                    </div>
                </div>
                <button type="submit" class="btn-primary mt-5">Update Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
