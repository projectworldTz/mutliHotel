@extends('layouts.owner')
@section('title', __('Invite Staff') . ' — ' . $hotel->name)
@section('page-title', __('Invite Staff Member'))

@section('content')
<div class="max-w-lg">
    <div class="mb-4"><a href="{{ route('owner.hotels.staff.index', $hotel) }}" class="btn-ghost btn-sm">{{ __('← Staff') }}</a></div>

    <form method="POST" action="{{ route('owner.hotels.staff.store', $hotel) }}">
        @csrf
        <div class="card p-6 space-y-4">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                {{ __("Enter the staff member's email. If they already have an account they will be added immediately using their existing login. If not, a new account will be created and you'll be shown a one-time temporary password to hand to them directly — no email required.") }}
            </p>

            <div>
                <label class="form-label">{{ __('Email Address') }} *</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-input @error('email') border-rose-500 @enderror"
                       placeholder="receptionist@example.com" required>
                @error('email') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">{{ __('Full Name') }} <span class="font-normal text-slate-400">({{ __('required for new accounts') }})</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-input @error('name') border-rose-500 @enderror"
                       placeholder="{{ __('Jane Doe') }}">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">{{ __('Position') }} *</label>
                <select name="position" class="form-select" required>
                    <option value="receptionist" {{ old('position') === 'receptionist' ? 'selected' : '' }}>{{ __('Receptionist') }}</option>
                    <option value="manager" {{ old('position') === 'manager' ? 'selected' : '' }}>{{ __('Manager') }}</option>
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('Add Staff Member') }}</button>
                <a href="{{ route('owner.hotels.staff.index', $hotel) }}" class="btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</div>
@endsection
