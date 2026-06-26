@extends('layouts.admin')
@section('title', 'New Coupon')
@section('page-title', 'Create Coupon')

@section('content')
<div class="max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('admin.coupons.index') }}" class="btn-ghost btn-sm">← Back to Coupons</a>
    </div>

    <form method="POST" action="{{ route('admin.coupons.store') }}">
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
                           placeholder="e.g. SUMMER25 — auto-generated if empty"
                           style="letter-spacing: 0.05em;">
                    @error('code') <p class="form-error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-400">
                        If scoped to a hotel, auto-generated codes use the hotel name as prefix (e.g. <strong>TRAN-X8KQ2</strong>).
                        Platform-wide codes use <strong>STAY-XXXXX</strong>.
                    </p>
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
                               placeholder="e.g. 15 for 15% or 5000 for TZS 5,000" required>
                        @error('value') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Scope --}}
                <div>
                    <label class="form-label">Scope</label>
                    <select name="hotel_id" class="form-select">
                        <option value="">Platform-wide (works on all hotels)</option>
                        @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                            {{ $hotel->name }} — {{ $hotel->city }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Restrict to a specific hotel, or leave as platform-wide.</p>
                </div>
            </div>

            <div class="card p-6 space-y-5">
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Restrictions</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Minimum Booking Amount (TZS)</label>
                        <input type="number" name="min_booking_amount" value="{{ old('min_booking_amount') }}"
                               min="0" step="1000" class="form-input"
                               placeholder="e.g. 50000 — no minimum if empty">
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
                <a href="{{ route('admin.coupons.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
