@extends('layouts.owner')
@section('title', 'Edit — ' . $hotel->name)
@section('page-title', 'Edit Hotel')

@section('content')
<div class="max-w-3xl">
    <div class="mb-4"><a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">← Back</a></div>

    <form method="POST" action="{{ route('owner.hotels.update', $hotel) }}">
        @csrf
        @method('PUT')
        <div class="space-y-5">
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Basic Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Hotel Name *</label>
                        <input type="text" name="name" value="{{ old('name', $hotel->name) }}"
                               class="form-input @error('name') border-rose-500 @enderror" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Category</label>
                            <select name="hotel_category_id" class="form-select">
                                <option value="">Select type…</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('hotel_category_id', $hotel->hotel_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Star Rating *</label>
                            <select name="star_rating" class="form-select" required>
                                @foreach([1,2,3,4,5] as $s)
                                <option value="{{ $s }}" {{ old('star_rating', $hotel->star_rating) == $s ? 'selected' : '' }}>{{ $s }} Star</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-textarea">{{ old('description', $hotel->description) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Location</h2>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Address *</label>
                        <input type="text" name="address" value="{{ old('address', $hotel->address) }}"
                               class="form-input" required>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="form-label">City *</label>
                            <input type="text" name="city" value="{{ old('city', $hotel->city) }}" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">State</label>
                            <input type="text" name="state" value="{{ old('state', $hotel->state) }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Country *</label>
                            <input type="text" name="country" value="{{ old('country', $hotel->country) }}" class="form-input" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Policies</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Check-in Time</label>
                        <input type="time" name="check_in_time" value="{{ old('check_in_time', $hotel->check_in_time) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Check-out Time</label>
                        <input type="time" name="check_out_time" value="{{ old('check_out_time', $hotel->check_out_time) }}" class="form-input">
                    </div>
                </div>
            </div>

            @if($amenities->isNotEmpty())
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Amenities</h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @php $hotelAmenityIds = $hotel->amenities->pluck('id')->toArray(); @endphp
                    @foreach($amenities as $amenity)
                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                        <input type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                               {{ in_array($amenity->id, old('amenity_ids', $hotelAmenityIds)) ? 'checked' : '' }}
                               class="rounded border-slate-300 text-navy">
                        {{ $amenity->name }}
                    </label>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
