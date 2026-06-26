@extends('layouts.owner')
@section('title', 'New Coupon — ' . $hotel->name)
@section('page-title', 'Create Coupon')

@section('content')
<div class="max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('owner.hotels.coupons.index', $hotel) }}" class="btn-ghost btn-sm">← Coupons</a>
    </div>

    <form method="POST" action="{{ route('owner.hotels.coupons.store', $hotel) }}">
        @csrf
        <div class="space-y-5">

            <div class="card p-6 space-y-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Coupon Details</h2>

                {{-- Code --}}
                <div>
                    <label class="form-label">Coupon Code
                        <span class="text-slate-400 font-normal">(leave blank to auto-generate)</span>
                    </label>
                    <input type="text" name="code" value="{{ old('code') }}"
                           class="form-input font-mono uppercase @error('code') border-rose-500 @enderror"
                           placeholder="Auto-generated: {{ strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $hotel->name), 0, 4)) }}-XXXXX"
                           style="letter-spacing: 0.05em;">
                    @error('code') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                {{-- Type + Value --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Discount Type *</label>
                        <select name="type" class="form-select @error('type') border-rose-500 @enderror" required>
                            <option value="percentage" {{ old('type') === 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                            <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (TZS)</option>
                        </select>
                        @error('type') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Value *</label>
                        <input type="number" name="value" value="{{ old('value') }}"
                               min="0.01" step="0.01"
                               class="form-input @error('value') border-rose-500 @enderror"
                               placeholder="e.g. 10 for 10% or 10000 for TZS 10,000" required>
                        @error('value') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Room Type Scope --}}
                @if($roomTypes->isNotEmpty())
                <div>
                    <label class="form-label">Apply To</label>
                    <select name="room_type_id" class="form-select">
                        <option value="">All room types</option>
                        @foreach($roomTypes as $rt)
                        <option value="{{ $rt->id }}" {{ old('room_type_id') == $rt->id ? 'selected' : '' }}>
                            {{ $rt->name }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Restrict to a specific room type, or apply to all rooms.</p>
                </div>
                @endif
            </div>

            <div class="card p-6 space-y-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Restrictions</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Minimum Booking Amount (TZS)</label>
                        <input type="number" name="min_booking_amount" value="{{ old('min_booking_amount') }}"
                               min="0" step="1000" class="form-input"
                               placeholder="No minimum if empty">
                        @error('min_booking_amount') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Max Uses</label>
                        <input type="number" name="max_uses" value="{{ old('max_uses') }}"
                               min="1" class="form-input"
                               placeholder="Unlimited if empty">
                        @error('max_uses') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                           min="{{ now()->addDay()->toDateString() }}"
                           class="form-input @error('expires_at') border-rose-500 @enderror">
                    @error('expires_at') <p class="form-error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-400">Leave empty for a coupon that never expires.</p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Create Coupon</button>
                <a href="{{ route('owner.hotels.coupons.index', $hotel) }}" class="btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
