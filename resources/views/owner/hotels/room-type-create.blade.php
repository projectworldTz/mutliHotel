@extends('layouts.owner')
@section('title', 'Add Room Type — ' . $hotel->name)
@section('page-title', 'Add Room Type')

@section('content')
<div class="max-w-2xl">
    <div class="mb-4"><a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost btn-sm">← Back</a></div>

    <form method="POST" action="{{ route('owner.hotels.room-types.store', $hotel) }}">
        @csrf
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">Room Type Details</h2>

            <div>
                <label class="form-label">Room Type Name *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-input @error('name') border-rose-500 @enderror"
                       placeholder="e.g. Deluxe King, Suite…" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="form-label">Bed Type *</label>
                    <select name="bed_type" class="form-select @error('bed_type') border-rose-500 @enderror" required>
                        @foreach(['Single','Twin','Double','Queen','King','Bunk'] as $bed)
                        <option value="{{ strtolower($bed) }}" {{ old('bed_type') === strtolower($bed) ? 'selected' : '' }}>{{ $bed }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Number of Beds *</label>
                    <input type="number" name="beds_count" value="{{ old('beds_count', 1) }}"
                           min="1" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Max Guests *</label>
                    <input type="number" name="max_guests" value="{{ old('max_guests', 2) }}"
                           min="1" max="20" class="form-input" required>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="form-label">Base Price / Night *</label>
                    <input type="number" name="base_price" value="{{ old('base_price') }}"
                           min="0" step="0.01" class="form-input @error('base_price') border-rose-500 @enderror" required>
                    @error('base_price') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Size (m²) <span class="font-normal text-slate-400">(optional)</span></label>
                    <input type="number" name="size_sqm" value="{{ old('size_sqm') }}"
                           min="0" step="0.1" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Description <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea name="description" rows="3" class="form-textarea"
                          placeholder="Describe this room type…">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="form-label">How many rooms of this type?</label>
                <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                       min="1" class="form-input w-28">
                <p class="mt-1 text-xs text-slate-500">We will create this many individual room records automatically.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Add Room Type</button>
                <a href="{{ route('owner.hotels.show', $hotel) }}" class="btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
