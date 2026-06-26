@extends('layouts.auth')
@section('title', 'Create Account')

@section('content')
<h2 class="text-3xl font-bold text-slate-900 dark:text-white">Create your account</h2>
<p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
    Already have an account?
    <a href="{{ route('login') }}" class="font-medium text-navy hover:text-navy-light dark:text-navy-light">Sign in</a>
</p>

<form method="POST" action="{{ route('register.submit') }}" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="name" class="form-label">Full name</label>
        <input type="text" id="name" name="name" value="{{ old('name') }}"
               autocomplete="name" required autofocus
               class="form-input @error('name') border-rose-500 @enderror"
               placeholder="Jane Doe">
        @error('name') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="form-label">Email address</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               autocomplete="email" required
               class="form-input @error('email') border-rose-500 @enderror"
               placeholder="you@example.com">
        @error('email') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password"
               autocomplete="new-password" required
               class="form-input @error('password') border-rose-500 @enderror"
               placeholder="Min. 8 characters">
        @error('password') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="password_confirmation" class="form-label">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               autocomplete="new-password" required
               class="form-input"
               placeholder="Repeat your password">
    </div>

    <button type="submit" class="btn-primary w-full btn-lg">
        Create Account
    </button>

    <p class="text-center text-xs text-slate-500 dark:text-slate-400">
        By signing up, you agree to our
        <a href="#" class="underline hover:text-slate-700">Terms of Service</a> and
        <a href="#" class="underline hover:text-slate-700">Privacy Policy</a>.
    </p>
</form>
@endsection
