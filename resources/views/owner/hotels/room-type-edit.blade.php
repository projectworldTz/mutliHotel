@extends('layouts.owner')
@section('title', __('Edit Room Type') . ' — ' . $hotel->name)
@section('page-title', __('Edit Room Type'))

@section('content')
<div class="max-w-2xl">
    <div class="mb-4"><a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">{{ __('← Back') }}</a></div>

    <form method="POST" action="{{ route('owner.hotels.room-types.update', [$hotel, $roomType]) }}">
        @csrf
        @method('PUT')
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ __('Room Type Details') }}</h2>

            <div>
                <label class="form-label">{{ __('Room Type Name') }} *</label>
                <input type="text" name="name" value="{{ old('name', $roomType->name) }}"
                       class="form-input @error('name') border-rose-500 @enderror"
                       placeholder="{{ __('e.g. Deluxe King, Suite…') }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="form-label">{{ __('Bed Type') }} *</label>
                    <select name="bed_type" class="form-select @error('bed_type') border-rose-500 @enderror" required>
                        @foreach(['Single','Twin','Double','Queen','King','Bunk'] as $bed)
                        <option value="{{ strtolower($bed) }}" {{ old('bed_type', $roomType->bed_type) === strtolower($bed) ? 'selected' : '' }}>{{ $bed }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('Number of Beds') }} *</label>
                    <input type="number" name="beds_count" value="{{ old('beds_count', $roomType->beds_count) }}"
                           min="1" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">{{ __('Max Guests') }} *</label>
                    <input type="number" name="max_guests" value="{{ old('max_guests', $roomType->max_guests) }}"
                           min="1" max="20" class="form-input" required>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">{{ __('Base Price / Night') }} *</label>
                    <input type="number" name="base_price" value="{{ old('base_price', $roomType->base_price) }}"
                           min="0" step="0.01" class="form-input @error('base_price') border-rose-500 @enderror" required>
                    @error('base_price') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">{{ __('Size (m²)') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                    <input type="number" name="size_sqm" value="{{ old('size_sqm', $roomType->size_sqm) }}"
                           min="0" step="0.1" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">{{ __('Description') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                <textarea name="description" rows="3" class="form-textarea"
                          placeholder="{{ __('Describe this room type…') }}">{{ old('description', $roomType->description) }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ __('Save Changes') }}</button>
                <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</div>
@endsection
