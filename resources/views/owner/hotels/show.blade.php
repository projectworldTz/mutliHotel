@extends('layouts.owner')
@section('title', $hotel->name)
@section('page-title', $hotel->name)

@section('content')
<div class="mb-4 flex items-center gap-2">
    <a href="{{ route('owner.hotels.index') }}" class="btn-ghost btn-sm">{{ __('← My Hotels') }}</a>
    <a href="{{ route('owner.hotels.staff.index', $hotel) }}" class="btn-ghost btn-sm ml-auto">{{ __('Staff') }}</a>
    @if($hotel->hasFeature('housekeeping'))
    <a href="{{ route('owner.housekeeping.index', $hotel) }}" class="btn-ghost btn-sm">🧹 {{ __('Housekeeping') }}</a>
    @endif
    @if($hotel->hasFeature('advanced_analytics'))
    <a href="{{ route('owner.analytics.index', $hotel) }}" class="btn-ghost btn-sm">📊 {{ __('Analytics') }}</a>
    @endif
    @php $pendingCancellations = \App\Models\CancellationApproval::forHotel($hotel->id)->pending()->count(); @endphp
    <a href="{{ route('owner.cancellation-approvals.index', $hotel) }}"
       class="relative btn-ghost btn-sm {{ $pendingCancellations ? 'text-amber-600 dark:text-amber-400' : '' }}">
        ⚠ {{ __('Cancellations') }}
        @if($pendingCancellations)
        <span class="ml-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1.5 text-[10px] font-bold text-white">{{ $pendingCancellations }}</span>
        @endif
    </a>
    @if($hotel->hasFeature('corporate_portal'))
    <a href="{{ route('owner.hotels.corporate.index', $hotel) }}" class="btn-ghost btn-sm">🏢 {{ __('Corporate') }}</a>
    @endif
    @php $pendingFeatureReqs = \App\Models\FeatureRequest::forHotel($hotel->id)->pending()->count(); @endphp
    <a href="{{ route('owner.hotels.features.index', $hotel) }}"
       class="relative btn-ghost btn-sm text-amber-600 dark:text-amber-400">
        ⭐ {{ __('Premium Features') }}
        @if($pendingFeatureReqs > 0)
        <span class="ml-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1.5 text-[10px] font-bold text-white">{{ $pendingFeatureReqs }}</span>
        @endif
    </a>
    <a href="{{ route('owner.hotels.edit', $hotel) }}" class="btn-outline btn-sm">{{ __('Edit Hotel') }}</a>
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
                <div class="flex items-center gap-2">
                    <span class="badge badge-{{ $hotel->status === 'active' ? 'active' : ($hotel->status === 'suspended' ? 'suspended' : 'pending-hotel') }}">
                        {{ ucfirst($hotel->status) }}
                    </span>
                    <form method="POST" action="{{ route('owner.hotels.toggle-online-booking', $hotel) }}">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors
                                {{ $hotel->online_booking_enabled
                                    ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50'
                                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-400 dark:hover:bg-slate-600' }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $hotel->online_booking_enabled ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                            {{ $hotel->online_booking_enabled ? 'Online Booking On' : 'Online Booking Off' }}
                        </button>
                    </form>
                </div>
            </div>
            @if($hotel->description)
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ $hotel->description }}</p>
            @endif
        </div>

        {{-- Payment Methods --}}
        @php
            $allMethods = [
                'airtel_money' => ['label' => 'Airtel Money', 'color' => 'bg-red-500',     'abbr' => 'AM'],
                'mpesa'        => ['label' => 'M-Pesa',       'color' => 'bg-emerald-600', 'abbr' => 'MP'],
                'halotel'      => ['label' => 'Halotel',      'color' => 'bg-orange-500',  'abbr' => 'HL'],
                'mix_by_yas'   => ['label' => 'Mix by Yas',   'color' => 'bg-blue-600',    'abbr' => 'MX'],
            ];
            $enabled = $hotel->enabledPaymentMethods();
        @endphp
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Payment Methods') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('Choose which mobile money providers guests can use to pay.') }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('owner.hotels.payment-methods.update', $hotel) }}">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach($allMethods as $key => $method)
                    @php $isEnabled = in_array($key, $enabled); @endphp
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3.5 transition-all
                                  {{ $isEnabled
                                     ? 'border-navy bg-navy/5 dark:border-navy-light dark:bg-navy/10'
                                     : 'border-slate-200 dark:border-slate-700' }}">
                        <input type="checkbox" name="payment_methods[]" value="{{ $key }}"
                               {{ $isEnabled ? 'checked' : '' }}
                               class="rounded border-slate-300 text-navy focus:ring-navy">
                        <span class="h-9 w-9 rounded-full {{ $method['color'] }} flex items-center justify-center text-white font-bold text-xs shrink-0">
                            {{ $method['abbr'] }}
                        </span>
                        <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $method['label'] }}</span>
                    </label>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ count($enabled) }} of {{ count($allMethods) }} {{ __('methods enabled') }}
                    </p>
                    <button type="submit" class="btn-outline btn-sm">
                        {{ __('Save Payment Methods') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- Hotel Photos --}}
        <div class="card p-6" x-data="{ selected: [] }">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Hotel Photos</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $hotel->images->count() }} photo{{ $hotel->images->count() !== 1 ? 's' : '' }} uploaded.
                        The <span class="font-medium">Cover</span> photo appears on search results and the booking page.
                    </p>
                </div>
            </div>

            {{-- Bulk delete bar --}}
            <div x-show="selected.length > 0" x-cloak
                 class="mb-4 flex items-center justify-between rounded-lg bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 px-3 py-2">
                <p class="text-xs font-medium text-rose-700 dark:text-rose-300">
                    <span x-text="selected.length"></span> {{ __('photo(s) selected') }}
                </p>
                <div class="flex items-center gap-2">
                    <button type="button" @click="selected = []" class="text-xs font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                        {{ __('Clear') }}
                    </button>
                    <form method="POST" action="{{ route('owner.hotels.images.bulk-destroy') }}"
                          data-loading data-confirm="{{ __('Delete the selected photos? This cannot be undone.') }}">
                        @csrf
                        @method('DELETE')
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="image_ids[]" :value="id">
                        </template>
                        <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 transition">
                            {{ __('Delete Selected') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Existing images grid --}}
            @if($hotel->images->isNotEmpty())
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 mb-6">
                @foreach($hotel->images->sortByDesc('is_featured') as $image)
                <div class="relative group rounded-xl overflow-hidden aspect-square bg-slate-100 dark:bg-slate-800"
                     x-data="{ confirming: false }"
                     :class="selected.includes({{ $image->id }}) && 'ring-2 ring-rose-500'">
                    <img src="{{ $image->url }}" alt="Hotel photo"
                         class="h-full w-full object-cover transition group-hover:brightness-75">

                    {{-- Select checkbox --}}
                    <label class="absolute top-1.5 right-1.5 z-10 flex h-5 w-5 items-center justify-center rounded-md bg-white/90 shadow cursor-pointer">
                        <input type="checkbox" value="{{ $image->id }}" x-model.number="selected" class="h-3.5 w-3.5 rounded text-rose-600 focus:ring-rose-500">
                    </label>

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
                <h3 class="font-bold text-slate-900 dark:text-white">{{ __('Room Types') }}</h3>
                <a href="{{ route('owner.hotels.room-types.create', $hotel) }}" class="btn-primary btn-sm">+ {{ __('Add Room Type') }}</a>
            </div>
            @if($hotel->roomTypes->isEmpty())
                <p class="p-5 text-sm text-slate-500">{{ __('No room types added yet.') }}</p>
            @else
            @foreach($hotel->roomTypes->load(['images', 'rooms']) as $rt)
            <div x-data="{ photosOpen: false, roomsOpen: false, selectedPhotos: [] }" class="border-b border-slate-100 dark:border-slate-700 last:border-0">
                {{-- Room type row --}}
                <div class="flex items-center gap-3 px-4 py-3">
                    {{-- Cover thumbnail --}}
                    @if($rt->images->isNotEmpty())
                    <img src="{{ $rt->images->firstWhere('is_featured', true)?->url ?? $rt->images->first()->url }}"
                         alt="{{ $rt->name }}"
                         class="h-12 w-16 rounded-lg object-cover shrink-0 border border-slate-200 dark:border-slate-600">
                    @else
                    <div class="h-12 w-16 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                        </svg>
                    </div>
                    @endif

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-900 dark:text-white text-sm">{{ $rt->name }}</p>
                        <p class="text-xs text-slate-500">{{ $rt->beds_count }}× {{ ucfirst($rt->bed_type) }} · {{ __('Max') }} {{ $rt->max_guests }} {{ __('guests') }} · {{ money($rt->base_price) }}/{{ __('night') }}</p>
                    </div>

                    {{-- Stats --}}
                    <div class="hidden sm:flex items-center gap-4 text-xs text-slate-500">
                        <span>{{ $rt->rooms->count() }} {{ __('rooms') }}</span>
                        <span>{{ $rt->images->count() }} {{ __('photos') }}</span>
                    </div>

                    {{-- Rooms toggle --}}
                    <button @click="roomsOpen = !roomsOpen"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21V6.75A2.25 2.25 0 016 4.5h12a2.25 2.25 0 012.25 2.25V21M3.75 21h16.5M3.75 21v-3.375c0-.621.504-1.125 1.125-1.125h14.25c.621 0 1.125.504 1.125 1.125V21M9 6.75h6M9 12h6"/>
                        </svg>
                        {{ __('Rooms') }}
                        @if($rt->rooms->isEmpty())
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        @endif
                        <svg class="h-3 w-3 transition-transform" :class="roomsOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Photos toggle --}}
                    <button @click="photosOpen = !photosOpen"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600 px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                        </svg>
                        {{ __('Photos') }}
                        <svg class="h-3 w-3 transition-transform" :class="photosOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                {{-- Expandable rooms panel --}}
                <div x-show="roomsOpen" x-collapse class="px-4 pb-4">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 p-4">

                        @if($rt->rooms->isEmpty())
                        <p class="text-xs text-rose-500 mb-3">{{ __('No physical rooms yet — guests cannot book this room type until you add at least one.') }}</p>
                        @else
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($rt->rooms->sortBy('room_number') as $room)
                            @php
                                $statusColor = match ($room->status) {
                                    'available'      => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'maintenance'    => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    'out_of_service' => 'bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400',
                                    default          => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-medium {{ $statusColor }}">
                                {{ __('Room') }} {{ $room->room_number }}
                                @if($room->floor)<span class="opacity-60">· {{ __('Floor') }} {{ $room->floor }}</span>@endif
                            </span>
                            @endforeach
                        </div>
                        @endif

                        {{-- Add room form --}}
                        <form method="POST" action="{{ route('owner.hotels.rooms.store', [$hotel, $rt]) }}" class="flex flex-wrap items-end gap-2">
                            @csrf
                            <div>
                                <label class="block text-[11px] font-medium text-slate-500 mb-1">{{ __('Room Number') }}</label>
                                <input type="text" name="room_number" required maxlength="20"
                                       class="w-28 rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-900 px-2.5 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-500 mb-1">{{ __('Floor') }}</label>
                                <input type="number" name="floor"
                                       class="w-20 rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-900 px-2.5 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-500 mb-1">{{ __('Status') }}</label>
                                <select name="status" class="rounded-lg border border-slate-300 dark:border-slate-600 dark:bg-slate-900 px-2.5 py-1.5 text-sm">
                                    <option value="available">{{ __('Available') }}</option>
                                    <option value="maintenance">{{ __('Maintenance') }}</option>
                                    <option value="out_of_service">{{ __('Out of Service') }}</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-primary btn-sm">{{ __('Add Room') }}</button>
                        </form>
                    </div>
                </div>

                {{-- Expandable photos panel --}}
                <div x-show="photosOpen" x-collapse class="px-4 pb-4">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 p-4">

                        {{-- Bulk delete bar --}}
                        <div x-show="selectedPhotos.length > 0" x-cloak
                             class="mb-3 flex items-center justify-between rounded-lg bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 px-3 py-1.5">
                            <p class="text-xs font-medium text-rose-700 dark:text-rose-300">
                                <span x-text="selectedPhotos.length"></span> {{ __('selected') }}
                            </p>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="selectedPhotos = []" class="text-xs font-medium text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                                    {{ __('Clear') }}
                                </button>
                                <form method="POST" action="{{ route('owner.hotels.room-type-images.bulk-destroy') }}"
                                      data-loading data-confirm="{{ __('Delete the selected photos? This cannot be undone.') }}">
                                    @csrf
                                    @method('DELETE')
                                    <template x-for="id in selectedPhotos" :key="id">
                                        <input type="hidden" name="image_ids[]" :value="id">
                                    </template>
                                    <button type="submit" class="rounded-lg bg-rose-600 px-3 py-1 text-xs font-semibold text-white hover:bg-rose-700 transition">
                                        {{ __('Delete Selected') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Existing photos --}}
                        @if($rt->images->isNotEmpty())
                        <div class="grid grid-cols-3 gap-2 sm:grid-cols-6 mb-4">
                            @foreach($rt->images->sortByDesc('is_featured') as $img)
                            <div class="relative group rounded-xl overflow-hidden aspect-square bg-slate-200 dark:bg-slate-700"
                                 x-data="{ confirming: false }"
                                 :class="selectedPhotos.includes({{ $img->id }}) && 'ring-2 ring-rose-500'">
                                <img src="{{ $img->url }}" alt="" class="h-full w-full object-cover transition group-hover:brightness-75">

                                {{-- Select checkbox --}}
                                <label class="absolute top-1 right-1 z-10 flex h-4 w-4 items-center justify-center rounded bg-white/90 shadow cursor-pointer">
                                    <input type="checkbox" value="{{ $img->id }}" x-model.number="selectedPhotos" class="h-3 w-3 rounded text-rose-600 focus:ring-rose-500">
                                </label>

                                @if($img->is_featured)
                                <div class="absolute top-1 left-1 rounded-full bg-gold px-1.5 py-0.5 text-[9px] font-bold text-white uppercase shadow">{{ __('Cover') }}</div>
                                @endif

                                <div class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 opacity-0 group-hover:opacity-100 transition">
                                    @unless($img->is_featured)
                                    <form method="POST" action="{{ route('owner.hotels.room-type-images.set-cover', $img) }}">
                                        @csrf
                                        <button class="rounded-lg bg-white/90 px-2 py-1 text-[10px] font-semibold text-slate-800 shadow hover:bg-gold hover:text-white transition whitespace-nowrap">
                                            ★ {{ __('Set Cover') }}
                                        </button>
                                    </form>
                                    @endunless

                                    <div>
                                        <button type="button" @click="confirming = true"
                                                class="rounded-lg bg-white/90 px-2 py-1 text-[10px] font-semibold text-slate-800 shadow hover:bg-rose-600 hover:text-white transition">
                                            {{ __('Delete') }}
                                        </button>
                                        <div x-show="confirming" x-cloak
                                             class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-black/80 rounded-xl p-2">
                                            <p class="text-[10px] text-white text-center font-medium">{{ __('Delete this photo?') }}</p>
                                            <div class="flex gap-1.5">
                                                <button @click="confirming = false" class="rounded-lg bg-white/20 px-2 py-1 text-[10px] text-white hover:bg-white/30 transition">{{ __('No') }}</button>
                                                <form method="POST" action="{{ route('owner.hotels.room-type-images.destroy', $img) }}">
                                                    @csrf @method('DELETE')
                                                    <button class="rounded-lg bg-rose-600 px-2 py-1 text-[10px] text-white font-semibold hover:bg-rose-700 transition">{{ __('Yes') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-xs text-slate-400 italic mb-3">{{ __('No photos yet. Upload some below.') }}</p>
                        @endif

                        {{-- Upload form --}}
                        <form method="POST"
                              action="{{ route('owner.hotels.room-types.images.store', [$hotel, $rt]) }}"
                              enctype="multipart/form-data"
                              x-data="{
                                  previews: [],
                                  addFiles(e) {
                                      Array.from(e.target.files).forEach(f => {
                                          const r = new FileReader();
                                          r.onload = ev => this.previews.push(ev.target.result);
                                          r.readAsDataURL(f);
                                      });
                                  }
                              }">
                            @csrf
                            <div class="flex items-center gap-3 flex-wrap">
                                <label class="cursor-pointer flex items-center gap-2 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:border-navy hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                                    </svg>
                                    {{ __('Choose Photos') }}
                                    <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                                           class="sr-only" @change="addFiles($event)">
                                </label>

                                <button type="submit" x-show="previews.length > 0" class="btn-primary btn-sm">
                                    {{ __('Upload') }} <span x-text="previews.length"></span> {{ __('Photo(s)') }}
                                </button>

                                <div x-show="previews.length > 0" class="flex gap-1.5">
                                    <template x-for="url in previews" :key="url">
                                        <img :src="url" class="h-10 w-10 rounded-lg object-cover border border-slate-200 dark:border-slate-600">
                                    </template>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
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
