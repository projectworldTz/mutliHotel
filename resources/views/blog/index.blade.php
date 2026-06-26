@extends('layouts.app')
@section('title', 'Travel Blog')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8 text-center">
    <svg class="mx-auto h-14 w-14 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
    </svg>
    <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Travel Blog</h1>
    <p class="mt-3 text-slate-500 dark:text-slate-400">Travel stories and tips — coming soon.</p>
    <a href="{{ route('hotels.index') }}" class="btn-primary mt-6 inline-block">Browse Hotels Instead</a>
</div>
@endsection
