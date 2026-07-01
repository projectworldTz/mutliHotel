@extends('layouts.receptionist')
@section('title', __('New Booking'))
@section('page-title', __('New Walk-in Booking'))

@section('content')
<div class="max-w-2xl">
    <div class="mb-4"><a href="{{ route('receptionist.bookings.index') }}" class="btn-ghost btn-sm">{{ __('← Bookings') }}</a></div>

    <form method="POST" action="{{ route('receptionist.bookings.store') }}">
        @csrf
        <div class="space-y-5">

            {{-- Guest details --}}
            <div class="card p-6 space-y-4">
                <h2 class="font-bold text-slate-900 dark:text-white">{{ __('Guest Details') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">{{ __('Full Name') }} *</label>
                        <input type="text" name="guest_name" value="{{ old('guest_name') }}"
                               class="form-input @error('guest_name') border-rose-500 @enderror" required>
                        @error('guest_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('Email') }} *</label>
                        <input type="email" name="guest_email" value="{{ old('guest_email') }}"
                               class="form-input @error('guest_email') border-rose-500 @enderror" required>
                        @error('guest_email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('Phone') }}</label>
                        <input type="text" name="guest_phone" value="{{ old('guest_phone') }}"
                               class="form-input" placeholder="+255 7xx xxx xxx">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Adult Guests') }} *</label>
                        <input type="number" name="guests_adults" value="{{ old('guests_adults', 1) }}"
                               min="1" max="20" class="form-input @error('guests_adults') border-rose-500 @enderror" required>
                        @error('guests_adults') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Stay details --}}
            <div class="card p-6 space-y-4">
                <h2 class="font-bold text-slate-900 dark:text-white">{{ __('Stay Details') }}</h2>
                <div>
                    <label class="form-label">{{ __('Room Type') }} *</label>
                    <select name="room_type_id" class="form-select @error('room_type_id') border-rose-500 @enderror" required>
                        <option value="">— {{ __('Select room type') }} —</option>
                        @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}" {{ old('room_type_id') == $rt->id ? 'selected' : '' }}>
                            {{ $rt->name }} — {{ money($rt->base_price) }}/night (max {{ $rt->max_guests }} guests)
                        </option>
                        @endforeach
                    </select>
                    @error('room_type_id') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">{{ __('Check-in') }} *</label>
                        <input type="date" name="check_in" value="{{ old('check_in', now()->toDateString()) }}"
                               min="{{ now()->toDateString() }}"
                               class="form-input @error('check_in') border-rose-500 @enderror" required>
                        @error('check_in') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('Check-out') }} *</label>
                        <input type="date" name="check_out" value="{{ old('check_out', now()->addDay()->toDateString()) }}"
                               min="{{ now()->addDay()->toDateString() }}"
                               class="form-input @error('check_out') border-rose-500 @enderror" required>
                        @error('check_out') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div>
                    <label class="form-label">{{ __('Payment Method') }} *</label>
                    <select name="payment_method" class="form-select @error('payment_method') border-rose-500 @enderror" required>
                        <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>{{ __('Cash') }}</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>{{ __('Bank Transfer') }}</option>
                        <option value="airtel_money" {{ old('payment_method') === 'airtel_money' ? 'selected' : '' }}>Airtel Money</option>
                        <option value="mpesa" {{ old('payment_method') === 'mpesa' ? 'selected' : '' }}>M-Pesa</option>
                    </select>
                    @error('payment_method') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Special Notes') }}</label>
                    <textarea name="notes" rows="3" class="form-textarea"
                              placeholder="{{ __('Any special requests or notes…') }}">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">{{ __('Create Booking') }}</button>
                <a href="{{ route('receptionist.bookings.index') }}" class="btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</div>
@endsection
