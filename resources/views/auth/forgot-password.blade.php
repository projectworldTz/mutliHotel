@extends('layouts.auth')
@section('title', 'Forgot Password')

@section('content')
<h2 class="text-3xl font-bold text-slate-900 dark:text-white">Forgot your password?</h2>
<p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
    Enter your email and we'll send you a link to reset it.
</p>

@if(session('status'))
    <div class="mt-6 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 flex items-start gap-3">
        <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('status') }}</p>
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
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

    <button type="submit" class="btn-primary w-full btn-lg">
        Send Reset Link
    </button>
</form>

<p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
    Remember your password?
    <a href="{{ route('login') }}" class="font-medium text-navy hover:text-navy-light dark:text-navy-light">Sign in</a>
</p>
@endsection
