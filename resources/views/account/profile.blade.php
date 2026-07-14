@extends('layouts.app')
@section('title', __('My Profile'))

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="page-header">
        <h1 class="page-title">{{ __('Profile Settings') }}</h1>
    </div>

    <div class="space-y-6">
        {{-- Update profile --}}
        <div class="card p-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">{{ __('Personal Information') }}</h2>
            <form method="POST" action="{{ route('account.profile.update') }}">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">{{ __('Full Name') }}</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                               class="form-input @error('name') border-rose-500 @enderror" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('Email Address') }}</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                               class="form-input @error('email') border-rose-500 @enderror" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('Phone') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                               class="form-input" placeholder="+1 555 000 0000">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="marketing_opt_in" value="0">
                        <input type="checkbox" name="marketing_opt_in" value="1"
                            {{ old('marketing_opt_in', auth()->user()->marketing_opt_in) ? 'checked' : '' }}
                            class="rounded accent-navy">
                        <span class="text-sm text-slate-700 dark:text-slate-200">{{ __('Send me promotions, offers, and news from hotels I\'ve stayed at or booked with') }}</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary mt-5">{{ __('Save Changes') }}</button>
            </form>
        </div>

        {{-- Change password --}}
        <div class="card p-6">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-5">{{ __('Change Password') }}</h2>
            <form method="POST" action="{{ route('account.password.update') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="form-label">{{ __('Current Password') }}</label>
                        <input type="password" name="current_password"
                               class="form-input @error('current_password') border-rose-500 @enderror"
                               placeholder="••••••••">
                        @error('current_password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('New Password') }}</label>
                        <input type="password" name="password"
                               class="form-input @error('password') border-rose-500 @enderror"
                               placeholder="{{ __('Min. 8 characters') }}">
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('Confirm New Password') }}</label>
                        <input type="password" name="password_confirmation"
                               class="form-input" placeholder="{{ __('Repeat new password') }}">
                    </div>
                </div>
                <button type="submit" class="btn-primary mt-5">{{ __('Update Password') }}</button>
            </form>
        </div>
    </div>
</div>
@endsection
