@extends('layouts.receptionist')
@section('title', __('Guest Messages'))
@section('page-title', $booking->user->name ?? __('Guest'))

@section('content')

<div class="mb-4"><a href="{{ route('receptionist.messages.index') }}" class="btn-ghost btn-sm">← {{ __('Messages') }}</a></div>

<div class="card p-6 max-w-2xl">
    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('Booking') }} {{ $booking->booking_number }}</p>

    <div class="space-y-3 max-h-[420px] overflow-y-auto mb-5 pr-1">
        @forelse($messages as $msg)
        <div class="flex {{ $msg->sender_type === 'staff' ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[75%] rounded-2xl px-4 py-2.5 text-sm {{ $msg->sender_type === 'staff' ? 'bg-navy text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-100' }}">
                <p>{{ $msg->message }}</p>
                <p class="mt-1 text-[10px] opacity-60">{{ $msg->created_at->format('d M, H:i') }}</p>
            </div>
        </div>
        @empty
        <p class="text-center text-slate-400 text-sm py-6">{{ __('No messages yet.') }}</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('receptionist.messages.store', $booking) }}" class="flex gap-2">
        @csrf
        <input type="text" name="message" required maxlength="1000" placeholder="{{ __('Type a reply…') }}" class="form-input flex-1">
        <button type="submit" class="btn-primary">{{ __('Send') }}</button>
    </form>
</div>

@endsection
