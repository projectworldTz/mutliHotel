@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<h2 class="text-3xl font-bold text-slate-900 dark:text-white">Welcome back</h2>
<p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
    Don't have an account?
    <a href="{{ route('register') }}" class="font-medium text-navy hover:text-navy-light dark:text-navy-light">Sign up free</a>
</p>

@if(session('status'))
    <div class="mt-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 flex items-start gap-3">
        <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('status') }}</p>
    </div>
@endif

<form method="POST" action="{{ route('login.submit') }}" class="mt-8 space-y-5">
    @csrf

    <div>
        <label for="email" class="form-label">Email address</label>
        <input type="email" id="email" name="email" value="{{ old('email') }}"
               autocomplete="email" required autofocus
               class="form-input @error('email') border-rose-500 @enderror"
               placeholder="you@example.com">
        @error('email')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password"
               autocomplete="current-password" required
               class="form-input @error('password') border-rose-500 @enderror"
               placeholder="••••••••">
        @error('password')
            <p class="form-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-navy">
            Remember me
        </label>
        <a href="{{ route('password.request') }}" class="text-sm font-medium text-navy hover:text-navy-light dark:text-navy-light">
            Forgot password?
        </a>
    </div>

    <button type="submit" class="btn-primary w-full btn-lg">
        Sign In
    </button>
</form>
@endsection
