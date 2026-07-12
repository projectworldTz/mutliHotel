@extends('layouts.admin')
@section('title', __('Add Hotel'))
@section('page-title', __('Add Hotel for Client'))

@section('content')
<div class="mb-5">
    <a href="{{ route('admin.hotels.index') }}" class="btn-ghost btn-sm">← {{ __('Back to Hotels') }}</a>
</div>

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.hotels.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="space-y-5">

            {{-- Owner --}}
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">{{ __('Client / Owner') }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">
                    {{ __('Choose the hotel-owner account this hotel will belong to.') }}
                </p>

                @if($owners->isEmpty())
                <p class="text-sm text-amber-600 dark:text-amber-400">
                    {{ __('No hotel-owner accounts exist yet.') }}
                    <a href="{{ route('admin.users.create') }}" class="underline font-medium">{{ __('Create one first') }}</a>.
                </p>
                @else
                <div>
                    <label class="form-label">{{ __('Owner') }} *</label>
                    <select name="owner_id" class="form-select @error('owner_id') border-rose-500 @enderror" required>
                        <option value="">{{ __('Select owner…') }}</option>
                        @foreach($owners as $owner)
                        <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                            {{ $owner->name }} ({{ $owner->email }}) — {{ $owner->ownedHotels->count() }}/{{ $owner->max_hotels ?? 1 }} {{ __('hotels') }}
                        </option>
                        @endforeach
                    </select>
                    @error('owner_id') <p class="form-error">{{ $message }}</p> @enderror
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        {{ __("Don't see the client?") }}
                        <a href="{{ route('admin.users.create') }}" class="underline">{{ __('Create a hotel-owner account') }}</a>.
                    </p>
                </div>
                @endif
            </div>

            {{-- Basic info --}}
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">{{ __('Basic Information') }}</h2>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">{{ __('Hotel Name') }} *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-input @error('name') border-rose-500 @enderror" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">{{ __('Category') }}</label>
                            <select name="hotel_category_id" class="form-select">
                                <option value="">{{ __('Select type…') }}</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('hotel_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">{{ __('Star Rating') }} *</label>
                            <select name="star_rating" class="form-select @error('star_rating') border-rose-500 @enderror" required>
                                @foreach([1,2,3,4,5] as $s)
                                <option value="{{ $s }}" {{ old('star_rating', 3) == $s ? 'selected' : '' }}>{{ $s }} {{ __('Star') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Description') }}</label>
                        <textarea name="description" rows="4" class="form-textarea"
                                  placeholder="{{ __('Describe the hotel…') }}">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Hotel Images --}}
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">{{ __('Hotel Photos') }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">
                    {{ __('Upload up to 8 photos. The first image is used as the cover photo. Accepted: JPG, PNG, WebP — max 4 MB each.') }}
                </p>
                @error('images')   <p class="mb-3 form-error">{{ $message }}</p> @enderror
                @error('images.*') <p class="mb-3 form-error">{{ $message }}</p> @enderror
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="form-input">
            </div>

            {{-- Location --}}
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">{{ __('Location') }}</h2>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">{{ __('Address') }} *</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="form-input @error('address') border-rose-500 @enderror" required>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="form-label">{{ __('City') }} *</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="form-input @error('city') border-rose-500 @enderror" required>
                        </div>
                        <div>
                            <label class="form-label">{{ __('State / Region') }}</label>
                            <input type="text" name="state" value="{{ old('state') }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">{{ __('Country') }} *</label>
                            <input type="text" name="country" value="{{ old('country') }}"
                                   class="form-input @error('country') border-rose-500 @enderror" required>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">{{ __('Latitude') }}</label>
                            <input type="number" step="any" min="-90" max="90" name="latitude"
                                   value="{{ old('latitude') }}" placeholder="-6.823959"
                                   class="form-input @error('latitude') border-rose-500 @enderror">
                            @error('latitude') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('Longitude') }}</label>
                            <input type="number" step="any" min="-180" max="180" name="longitude"
                                   value="{{ old('longitude') }}" placeholder="39.133622"
                                   class="form-input @error('longitude') border-rose-500 @enderror">
                            @error('longitude') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Policies --}}
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">{{ __('Policies') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">{{ __('Check-in Time') }}</label>
                        <input type="time" name="check_in_time" value="{{ old('check_in_time', '14:00') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">{{ __('Check-out Time') }}</label>
                        <input type="time" name="check_out_time" value="{{ old('check_out_time', '11:00') }}" class="form-input">
                    </div>
                </div>
            </div>

            {{-- Amenities --}}
            @if($amenities->isNotEmpty())
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">{{ __('Amenities') }}</h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach($amenities as $amenity)
                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                               {{ in_array($amenity->id, old('amenity_ids', [])) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-navy">
                        {{ $amenity->name }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="btn-primary" {{ $owners->isEmpty() ? 'disabled' : '' }}>{{ __('Create Hotel') }}</button>
                <a href="{{ route('admin.hotels.index') }}" class="btn-ghost">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</div>
@endsection
