@extends('layouts.app')
@section('title', __('Share Your Feedback'))

@section('content')
<div class="mx-auto max-w-lg px-4 py-12 sm:px-6 lg:px-8">

    @if($survey->isResponded())
    <div class="card p-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
            <svg class="h-7 w-7 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ __('Thank you!') }}</h1>
        <p class="text-slate-500 dark:text-slate-400">{{ __('Your feedback for :hotel has already been recorded — we appreciate it.', ['hotel' => $survey->hotel->name]) }}</p>
    </div>
    @else
    <div class="card p-8">
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('How was your stay?') }}</h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1">{{ $survey->hotel->name }}</p>
        </div>

        @if($errors->any())
        <div class="mb-5 alert-error">
            @foreach($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('survey.store', $survey->token) }}" x-data="{ rating: 0 }">
            @csrf

            <div class="flex justify-center gap-2 mb-6">
                @for($i = 1; $i <= 5; $i++)
                <label class="cursor-pointer">
                    <input type="radio" name="rating" value="{{ $i }}" x-model="rating" class="sr-only" required>
                    <svg class="h-10 w-10 transition-colors" :class="rating >= {{ $i }} ? 'text-gold' : 'text-slate-200 dark:text-slate-700'" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 00-.363 1.118l1.286 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.285-3.957a1 1 0 00-.362-1.118L2.062 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 00.95-.69l1.286-3.958z"/>
                    </svg>
                </label>
                @endfor
            </div>
            @error('rating') <p class="form-error text-center mb-4">{{ $message }}</p> @enderror

            <div class="mb-5">
                <label class="form-label">{{ __('Anything you\'d like to share?') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                <textarea name="comment" rows="4" class="form-input" placeholder="{{ __('Tell us more about your stay…') }}">{{ old('comment') }}</textarea>
            </div>

            <button type="submit" class="btn-primary w-full">{{ __('Submit Feedback') }}</button>
        </form>
    </div>
    @endif
</div>
@endsection
