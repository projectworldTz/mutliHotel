@extends('layouts.owner')
@section('title', 'Add Hotel')
@section('page-title', 'List a New Hotel')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('owner.hotels.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="space-y-5">

            {{-- Basic info --}}
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Basic Information</h2>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Hotel Name *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-input @error('name') border-rose-500 @enderror" required>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Category</label>
                            <select name="hotel_category_id" class="form-select">
                                <option value="">Select type…</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('hotel_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Star Rating *</label>
                            <select name="star_rating" class="form-select @error('star_rating') border-rose-500 @enderror" required>
                                @foreach([1,2,3,4,5] as $s)
                                <option value="{{ $s }}" {{ old('star_rating', 3) == $s ? 'selected' : '' }}>{{ $s }} Star</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="4" class="form-textarea"
                                  placeholder="Describe your hotel…">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Hotel Images --}}
            <div class="card p-6"
                 x-data="{
                     previews: [],
                     addFiles(event) {
                         const files = Array.from(event.target.files);
                         files.forEach(file => {
                             const reader = new FileReader();
                             reader.onload = e => this.previews.push({ url: e.target.result, name: file.name });
                             reader.readAsDataURL(file);
                         });
                     },
                     removePreview(index) {
                         this.previews.splice(index, 1);
                         const dt = new DataTransfer();
                         Array.from(this.$refs.fileInput.files)
                             .filter((_, i) => i !== index)
                             .forEach(f => dt.items.add(f));
                         this.$refs.fileInput.files = dt.files;
                     },
                     isDragging: false
                 }">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Hotel Photos</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5">
                    Upload up to 8 photos. The <span class="font-medium text-slate-700 dark:text-slate-300">first image</span> will be used as the cover / profile photo.
                    Accepted: JPG, PNG, WebP — max 4 MB each.
                </p>

                @error('images')   <p class="mb-3 form-error">{{ $message }}</p> @enderror
                @error('images.*') <p class="mb-3 form-error">{{ $message }}</p> @enderror

                {{-- Drop zone --}}
                <label for="hotel-images"
                       class="relative flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed cursor-pointer transition-colors
                              border-slate-300 dark:border-slate-600
                              hover:border-navy dark:hover:border-navy-light
                              hover:bg-slate-50 dark:hover:bg-slate-800/50"
                       :class="isDragging ? 'border-navy bg-slate-50 dark:border-navy-light dark:bg-slate-800/50' : ''"
                       @dragover.prevent="isDragging = true"
                       @dragleave.prevent="isDragging = false"
                       @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; addFiles({ target: $refs.fileInput })"
                       style="min-height: 10rem; padding: 2rem;">

                    <svg class="h-10 w-10 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M6.75 18H17.25M3 3h18M9 3.75v1.5"/>
                    </svg>
                    <div class="text-center">
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                            Drag &amp; drop photos here, or <span class="text-navy dark:text-navy-light underline">click to browse</span>
                        </p>
                        <p class="mt-1 text-xs text-slate-400">JPG, PNG or WebP · Up to 8 files · Max 4 MB each</p>
                    </div>

                    <input type="file" id="hotel-images" name="images[]"
                           multiple accept="image/jpeg,image/png,image/webp"
                           class="sr-only"
                           x-ref="fileInput"
                           @change="addFiles($event)">
                </label>

                {{-- Preview grid --}}
                <div x-show="previews.length > 0"
                     class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <template x-for="(img, index) in previews" :key="index">
                        <div class="relative group rounded-xl overflow-hidden aspect-square bg-slate-100 dark:bg-slate-800">
                            <img :src="img.url" :alt="img.name"
                                 class="h-full w-full object-cover">

                            {{-- Cover badge on first image --}}
                            <div x-show="index === 0"
                                 class="absolute top-1.5 left-1.5 rounded-full bg-gold px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wide shadow">
                                Cover
                            </div>

                            {{-- Remove button --}}
                            <button type="button"
                                    @click="removePreview(index)"
                                    class="absolute top-1.5 right-1.5 hidden group-hover:flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white hover:bg-rose-600 transition">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>

                            {{-- Image name --}}
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-2 py-1.5 opacity-0 group-hover:opacity-100 transition">
                                <p class="text-[10px] text-white truncate" x-text="img.name"></p>
                            </div>
                        </div>
                    </template>
                </div>

                <p x-show="previews.length > 0"
                   class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                    <span x-text="previews.length"></span> photo<span x-show="previews.length !== 1">s</span> selected.
                    You can add more or drag to reorder.
                </p>
            </div>

            {{-- Location --}}
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Location</h2>
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Address *</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               class="form-input @error('address') border-rose-500 @enderror" required>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="form-label">City *</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="form-input @error('city') border-rose-500 @enderror" required>
                        </div>
                        <div>
                            <label class="form-label">State / Region</label>
                            <input type="text" name="state" value="{{ old('state') }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Country *</label>
                            <input type="text" name="country" value="{{ old('country') }}"
                                   class="form-input @error('country') border-rose-500 @enderror" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Policies --}}
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Policies</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Check-in Time</label>
                        <input type="time" name="check_in_time" value="{{ old('check_in_time', '14:00') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Check-out Time</label>
                        <input type="time" name="check_out_time" value="{{ old('check_out_time', '11:00') }}" class="form-input">
                    </div>
                </div>
            </div>

            {{-- Amenities --}}
            @if($amenities->isNotEmpty())
            <div class="card p-6">
                <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Amenities</h2>
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
                <button type="submit" class="btn-primary">Submit for Review</button>
                <a href="{{ route('owner.hotels.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
