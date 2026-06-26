@extends('layouts.owner')
@section('title', $hotel->name)
@section('page-title', $hotel->name)

@section('content')
<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('owner.hotels.index') }}" class="btn-ghost btn-sm">← My Hotels</a>
    <a href="{{ route('owner.hotels.coupons.index', $hotel) }}" class="btn-ghost btn-sm ml-auto">Coupons</a>
    <a href="{{ route('owner.hotels.staff.index', $hotel) }}" class="btn-ghost btn-sm">Staff</a>
    <a href="{{ route('owner.hotels.edit', $hotel) }}" class="btn-outline btn-sm">Edit Hotel</a>
</div>

@if(session('success'))
<div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 flex items-start gap-3">
    <svg class="h-5 w-5 text-emerald-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ session('success') }}</p>
</div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-5">
        {{-- Info --}}
        <div class="card p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $hotel->name }}</h2>
                    <p class="text-slate-500">{{ $hotel->city }}, {{ $hotel->country }}</p>
                </div>
                <span class="badge badge-{{ $hotel->status === 'active' ? 'active' : ($hotel->status === 'suspended' ? 'suspended' : 'pending-hotel') }}">
                    {{ ucfirst($hotel->status) }}
                </span>
            </div>
            @if($hotel->description)
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $hotel->description }}</p>
            @endif
        </div>

        {{-- Hotel Photos --}}
        <div class="card p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Hotel Photos</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $hotel->images->count() }} photo{{ $hotel->images->count() !== 1 ? 's' : '' }} uploaded.
                        The <span class="font-medium">Cover</span> photo appears on search results and the booking page.
                    </p>
                </div>
            </div>

            {{-- Existing images grid --}}
            @if($hotel->images->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
                @foreach($hotel->images->sortByDesc('is_featured') as $image)
                <div class="relative group rounded-xl overflow-hidden aspect-square bg-slate-100 dark:bg-slate-800"
                     x-data="{ confirming: false }">
                    <img src="{{ $image->url }}" alt="Hotel photo"
                         class="h-full w-full object-cover transition group-hover:brightness-75">

                    {{-- Cover badge --}}
                    @if($image->is_featured)
                    <div class="absolute top-1.5 left-1.5 rounded-full bg-gold px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wide shadow">
                        Cover
                    </div>
                    @endif

                    {{-- Action buttons — visible on hover --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition">
                        {{-- Set as cover --}}
                        @unless($image->is_featured)
                        <form method="POST" action="{{ route('owner.hotels.images.set-cover', $image) }}">
                            @csrf
                            <button type="submit"
                                    class="flex items-center gap-1.5 rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-slate-800 shadow hover:bg-gold hover:text-white transition">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                                </svg>
                                Set Cover
                            </button>
                        </form>
                        @endunless

                        {{-- Delete --}}
                        <div>
                            <button type="button"
                                    @click="confirming = true"
                                    class="flex items-center gap-1.5 rounded-lg bg-white/90 px-3 py-1.5 text-xs font-semibold text-slate-800 shadow hover:bg-rose-600 hover:text-white transition">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                </svg>
                                Delete
                            </button>

                            {{-- Inline confirm --}}
                            <div x-show="confirming" x-cloak
                                 class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/80 rounded-xl p-3">
                                <p class="text-xs text-white text-center font-medium">Delete this photo?</p>
                                <div class="flex gap-2">
                                    <button @click="confirming = false"
                                            class="rounded-lg bg-white/20 px-3 py-1.5 text-xs text-white hover:bg-white/30 transition">
                                        Cancel
                                    </button>
                                    <form method="POST" action="{{ route('owner.hotels.images.destroy', $image) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs text-white font-semibold hover:bg-rose-700 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400 dark:text-slate-500 mb-5 italic">No photos uploaded yet. Add some below to attract guests.</p>
            @endif

            {{-- Upload more photos --}}
            <div x-data="{
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
                <form method="POST"
                      action="{{ route('owner.hotels.images.store', $hotel) }}"
                      enctype="multipart/form-data">
                    @csrf

                    @error('images')   <p class="mb-2 form-error">{{ $message }}</p> @enderror
                    @error('images.*') <p class="mb-2 form-error">{{ $message }}</p> @enderror

                    <label for="upload-images"
                           class="relative flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed cursor-pointer transition-colors
                                  border-slate-300 dark:border-slate-600
                                  hover:border-navy dark:hover:border-navy-light
                                  hover:bg-slate-50 dark:hover:bg-slate-800/50"
                           :class="isDragging ? 'border-navy bg-slate-50 dark:border-navy-light dark:bg-slate-800/50' : ''"
                           @dragover.prevent="isDragging = true"
                           @dragleave.prevent="isDragging = false"
                           @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; addFiles({ target: $refs.fileInput })"
                           style="min-height: 8rem; padding: 1.5rem;">
                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                        </svg>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Drag &amp; drop photos, or <span class="text-navy dark:text-navy-light underline font-medium">click to browse</span>
                        </p>
                        <p class="text-xs text-slate-400">JPG, PNG or WebP · Up to 8 files · Max 4 MB each</p>
                        <input type="file" id="upload-images" name="images[]"
                               multiple accept="image/jpeg,image/png,image/webp"
                               class="sr-only"
                               x-ref="fileInput"
                               @change="addFiles($event)">
                    </label>

                    {{-- New image previews --}}
                    <div x-show="previews.length > 0" class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-6">
                        <template x-for="(img, index) in previews" :key="index">
                            <div class="relative group rounded-lg overflow-hidden aspect-square bg-slate-100 dark:bg-slate-800">
                                <img :src="img.url" class="h-full w-full object-cover">
                                <button type="button" @click="removePreview(index)"
                                        class="absolute top-1 right-1 hidden group-hover:flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-white hover:bg-rose-600 transition">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <button type="submit"
                                x-show="previews.length > 0"
                                class="btn-primary btn-sm">
                            Upload <span x-text="previews.length"></span> Photo<span x-show="previews.length !== 1">s</span>
                        </button>
                        <p x-show="previews.length === 0" class="text-xs text-slate-400 dark:text-slate-500">
                            Select photos above to upload them.
                        </p>
                    </div>
                </form>
            </div>
        </div>

        {{-- Room types --}}
        <div class="card">
            <div class="flex items-center justify-between p-5 border-b border-slate-100 dark:border-slate-700">
                <h3 class="font-bold text-slate-900 dark:text-white">Room Types</h3>
                <a href="{{ route('owner.hotels.room-types.create', $hotel) }}" class="btn-primary btn-sm">+ Add Room Type</a>
            </div>
            @if($hotel->roomTypes->isEmpty())
                <p class="p-5 text-sm text-slate-500">No room types added yet.</p>
            @else
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Name</th><th>Bed</th><th>Max Guests</th><th>Base Price</th><th>Rooms</th></tr></thead>
                    <tbody>
                        @foreach($hotel->roomTypes as $rt)
                        <tr class="tr-hover">
                            <td class="font-medium">{{ $rt->name }}</td>
                            <td>{{ $rt->beds_count }}× {{ $rt->bed_type }}</td>
                            <td>{{ $rt->max_guests }}</td>
                            <td>TZS {{ number_format($rt->base_price, 0) }}</td>
                            <td>{{ $rt->rooms->count() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        <div class="card p-5 text-sm space-y-2">
            <h3 class="font-bold text-slate-900 dark:text-white mb-2">Hotel Details</h3>
            <div class="flex justify-between"><span class="text-slate-500">Stars</span><span>{{ $hotel->star_rating }}★</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Check-in</span><span>{{ $hotel->check_in_time ?? '14:00' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Check-out</span><span>{{ $hotel->check_out_time ?? '11:00' }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Category</span><span>{{ $hotel->category->name ?? '—' }}</span></div>
        </div>
        <a href="{{ route('hotels.show', $hotel) }}" target="_blank"
           class="btn-outline w-full text-center block btn-sm">View Public Page →</a>
    </div>
</div>
@endsection
