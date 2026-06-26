@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<h2 class="text-3xl font-bold text-slate-900 dark:text-white">Set new password</h2>
<p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
    Choose a strong password for your account.
</p>

<form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div>
        <label for="email" class="form-label">Email address</label>
        <input type="email" id="email" name="email" value="{{ old('email', $email) }}"
               autocomplete="email" required
               class="form-input @error('email') border-rose-500 @enderror"
               placeholder="you@example.com">
        @error('email')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="form-label">New password</label>
        <input type="password" id="password" name="password"
               autocomplete="new-password" required
               class="form-input @error('password') border-rose-500 @enderror"
               placeholder="Min. 8 characters">
        @error('password')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password_confirmation" class="form-label">Confirm new password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               autocomplete="new-password" required
               class="form-input"
               placeholder="Repeat your password">
    </div>

    <button type="submit" class="btn-primary w-full btn-lg">
        Reset Password
    </button>
</form>

<p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
    <a href="{{ route('login') }}" class="font-medium text-navy hover:text-navy-light dark:text-navy-light">← Back to sign in</a>
</p>
@endsection
