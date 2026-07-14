@extends('layouts.owner')
@section('title', 'New Campaign — ' . $hotel->name)
@section('page-title', 'New Campaign')

@section('content')

<div class="mb-4"><a href="{{ route('owner.campaigns.index', $hotel) }}" class="btn-ghost btn-sm">← Campaigns</a></div>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('owner.campaigns.store', $hotel) }}" class="card p-6 space-y-4">
        @csrf

        <div>
            <label class="form-label">Subject <span class="text-rose-500">*</span></label>
            <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="150"
                   class="form-input @error('subject') border-rose-500 @enderror"
                   placeholder="e.g. 20% off your next stay with us">
            @error('subject') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Message <span class="text-rose-500">*</span></label>
            <textarea name="body" rows="8" required maxlength="5000"
                      class="form-input @error('body') border-rose-500 @enderror"
                      placeholder="Write your newsletter or promotion here…">{{ old('body') }}</textarea>
            @error('body') <p class="form-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="form-label">Audience <span class="text-rose-500">*</span></label>
            <div class="space-y-2">
                <label class="flex items-center justify-between gap-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 p-3.5 cursor-pointer hover:border-slate-300 dark:hover:border-slate-600">
                    <span class="flex items-center gap-3">
                        <input type="radio" name="audience" value="all_guests" {{ old('audience', 'all_guests') === 'all_guests' ? 'checked' : '' }} class="text-navy">
                        <span class="text-sm font-medium text-slate-900 dark:text-white">All Guests</span>
                    </span>
                    <span class="text-xs text-slate-400">{{ $audienceCounts['all_guests'] }} recipient(s)</span>
                </label>
                <label class="flex items-center justify-between gap-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 p-3.5 cursor-pointer hover:border-slate-300 dark:hover:border-slate-600">
                    <span class="flex items-center gap-3">
                        <input type="radio" name="audience" value="past_guests" {{ old('audience') === 'past_guests' ? 'checked' : '' }} class="text-navy">
                        <span class="text-sm font-medium text-slate-900 dark:text-white">Past Guests</span>
                    </span>
                    <span class="text-xs text-slate-400">{{ $audienceCounts['past_guests'] }} recipient(s)</span>
                </label>
                <label class="flex items-center justify-between gap-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 p-3.5 cursor-pointer hover:border-slate-300 dark:hover:border-slate-600">
                    <span class="flex items-center gap-3">
                        <input type="radio" name="audience" value="upcoming_guests" {{ old('audience') === 'upcoming_guests' ? 'checked' : '' }} class="text-navy">
                        <span class="text-sm font-medium text-slate-900 dark:text-white">Upcoming Guests</span>
                    </span>
                    <span class="text-xs text-slate-400">{{ $audienceCounts['upcoming_guests'] }} recipient(s)</span>
                </label>
            </div>
            <p class="mt-2 text-xs text-slate-400">Only guests who've opted in to promotional email in their account settings are counted.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" name="send_now" value="0" class="btn-outline flex-1">Save as Draft</button>
            <button type="submit" name="send_now" value="1" class="btn-primary flex-1"
                    onclick="return confirm('Send this campaign now? This cannot be undone.')">Send Now</button>
        </div>
    </form>
</div>

@endsection
