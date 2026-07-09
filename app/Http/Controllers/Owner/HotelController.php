<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\HotelCategory;
use App\Models\HotelImage;
use App\Models\HotelVideo;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use Illuminate\Support\Facades\Storage;
use App\Services\HotelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function __construct(private HotelService $hotelService) {}

    public function index()
    {
        $hotels = $this->hotelService->getForOwner(auth()->user(), 15);

        return view('owner.hotels.index', compact('hotels'));
    }

    public function create()
    {
        if (! auth()->user()->canAddHotel()) {
            return redirect()->route('owner.hotels.index')
                ->with('error', 'You have reached your hotel limit. Please contact the platform administrator to register additional properties.');
        }

        $categories = HotelCategory::active()->orderBy('name')->get();
        $amenities  = Amenity::orderBy('category')->orderBy('name')->get();

        return view('owner.hotels.create', compact('categories', 'amenities'));
    }

    public function store(StoreHotelRequest $request)
    {
        if (! auth()->user()->canAddHotel()) {
            return redirect()->route('owner.hotels.index')
                ->with('error', 'You have reached your hotel limit. Please contact the platform administrator to register additional properties.');
        }

        $hotel = $this->hotelService->create($request->validated(), auth()->user());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $this->hotelService->uploadImage($hotel, $file, $index === 0);
            }
        }

        return redirect()->route('owner.hotels.show', $hotel)
            ->with('success', 'Hotel submitted! It will be visible after admin approval.');
    }

    public function show(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $hotel->loadMissing(['images', 'videos', 'amenities', 'roomTypes.images', 'roomTypes.amenities']);
        $stats = $this->hotelService->stats();

        return view('owner.hotels.show', compact('hotel'));
    }

    public function edit(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $categories = HotelCategory::active()->orderBy('name')->get();
        $amenities  = Amenity::orderBy('category')->orderBy('name')->get();
        $hotel->loadMissing(['images', 'amenities']);

        return view('owner.hotels.edit', compact('hotel', 'categories', 'amenities'));
    }

    public function update(StoreHotelRequest $request, Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $this->hotelService->update($hotel, $request->validated());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $this->hotelService->uploadImage($hotel, $file);
            }
        }

        return back()->with('success', 'Hotel updated successfully.');
    }

    // ── Room type management ──────────────────────────────────────────────────

    public function createRoomType(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $amenities = Amenity::orderBy('category')->orderBy('name')->get();

        return view('owner.hotels.room-type-create', compact('hotel', 'amenities'));
    }

    public function storeRoomType(Request $request, Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price'  => 'required|numeric|min:0',
            'max_guests'  => 'required|integer|min:1|max:20',
            'bed_type'    => 'required|string|max:50',
            'beds_count'  => 'required|integer|min:1',
            'size_sqm'    => 'nullable|numeric|min:0',
            'view_type'   => 'nullable|string|max:100',
            'smoking'     => 'boolean',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,id',
        ]);

        $roomType = $this->hotelService->createRoomType($hotel, $data);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store("room-types/{$roomType->id}", 'public');
                $roomType->images()->create([
                    'path'       => $path,
                    'url'        => Storage::disk('public')->url($path),
                    'sort_order' => $index,
                ]);
            }
        }

        return back()->with('success', 'Room type created successfully.');
    }

    public function storeRoom(Request $request, Hotel $hotel, RoomType $roomType)
    {
        $this->authorizeHotel($hotel);
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        $data = $request->validate([
            'room_number' => 'required|string|max:20',
            'floor'       => 'nullable|integer',
            'status'      => 'in:available,maintenance,out_of_service',
        ]);

        $this->hotelService->createRoom($hotel, $roomType, $data);

        return back()->with('success', "Room {$data['room_number']} created.");
    }

    public function editRoomType(Hotel $hotel, RoomType $roomType)
    {
        $this->authorizeHotel($hotel);
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        $amenities = Amenity::orderBy('category')->orderBy('name')->get();

        return view('owner.hotels.room-type-edit', compact('hotel', 'roomType', 'amenities'));
    }

    public function updateRoomType(Request $request, Hotel $hotel, RoomType $roomType)
    {
        $this->authorizeHotel($hotel);
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price'  => 'required|numeric|min:0',
            'max_guests'  => 'required|integer|min:1|max:20',
            'bed_type'    => 'required|string|max:50',
            'beds_count'  => 'required|integer|min:1',
            'size_sqm'    => 'nullable|numeric|min:0',
            'view_type'   => 'nullable|string|max:100',
            'smoking'     => 'boolean',
            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'exists:amenities,id',
        ]);

        $this->hotelService->updateRoomType($roomType, $data);

        return redirect()->route('owner.hotels.show', $hotel)->with('success', 'Room type updated successfully.');
    }

    public function destroyRoomType(Hotel $hotel, RoomType $roomType)
    {
        $this->authorizeHotel($hotel);
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        try {
            $this->hotelService->deleteRoomType($roomType);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['room_type' => $e->getMessage()]);
        }

        return back()->with('success', 'Room type deleted.');
    }

    public function updateRoom(Request $request, Hotel $hotel, RoomType $roomType, Room $room)
    {
        $this->authorizeHotel($hotel);
        abort_if($roomType->hotel_id !== $hotel->id || $room->room_type_id !== $roomType->id, 404);

        $data = $request->validate([
            'room_number' => 'required|string|max:20',
            'floor'       => 'nullable|integer',
            'status'      => 'in:available,maintenance,out_of_service',
        ]);

        $this->hotelService->updateRoom($room, $data);

        return back()->with('success', "Room {$data['room_number']} updated.");
    }

    public function destroyRoom(Hotel $hotel, RoomType $roomType, Room $room)
    {
        $this->authorizeHotel($hotel);
        abort_if($roomType->hotel_id !== $hotel->id || $room->room_type_id !== $roomType->id, 404);

        try {
            $this->hotelService->deleteRoom($room);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['room' => $e->getMessage()]);
        }

        return back()->with('success', "Room {$room->room_number} deleted.");
    }

    // ── Image management ──────────────────────────────────────────────────────

    public function storeImages(Request $request, Hotel $hotel): RedirectResponse
    {
        $this->authorizeHotel($hotel);

        $request->validate([
            'images'   => ['required', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        foreach ($request->file('images') as $file) {
            $this->hotelService->uploadImage($hotel, $file);
        }

        return back()->with('success', count($request->file('images')) . ' photo(s) uploaded successfully.');
    }

    public function setCoverImage(HotelImage $image): RedirectResponse
    {
        $hotel = $image->hotel;
        $this->authorizeHotel($hotel);

        $hotel->images()->update(['is_featured' => false]);
        $image->update(['is_featured' => true]);

        return back()->with('success', 'Cover photo updated.');
    }

    public function deleteImage(HotelImage $image): RedirectResponse
    {
        $hotel = $image->hotel;
        $this->authorizeHotel($hotel);

        $this->hotelService->deleteImage($image);

        return back()->with('success', 'Photo deleted.');
    }

    public function deleteImages(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'image_ids'   => ['required', 'array', 'min:1'],
            'image_ids.*' => ['integer', 'exists:hotel_images,id'],
        ]);

        $images = HotelImage::whereIn('id', $data['image_ids'])->get();

        foreach ($images as $image) {
            $this->authorizeHotel($image->hotel);
            $this->hotelService->deleteImage($image);
        }

        return back()->with('success', $images->count() . ' photo(s) deleted.');
    }

    // ── Room-type image management ────────────────────────────────────────────

    public function storeRoomTypeImages(Request $request, Hotel $hotel, RoomType $roomType): RedirectResponse
    {
        $this->authorizeHotel($hotel);
        abort_if($roomType->hotel_id !== $hotel->id, 404);

        $request->validate([
            'images'   => ['required', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $nextOrder = $roomType->images()->max('sort_order') + 1;

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store("room-types/{$roomType->id}", 'public');
            $roomType->images()->create([
                'path'        => $path,
                'url'         => Storage::disk('public')->url($path),
                'sort_order'  => $nextOrder + $index,
                'is_featured' => $roomType->images()->count() === 0,
            ]);
        }

        return back()->with('success', count($request->file('images')) . ' photo(s) uploaded.');
    }

    public function setCoverRoomTypeImage(RoomImage $image): RedirectResponse
    {
        $roomType = $image->roomType;
        $this->authorizeHotel($roomType->hotel);

        $roomType->images()->update(['is_featured' => false]);
        $image->update(['is_featured' => true]);

        return back()->with('success', 'Cover photo updated.');
    }

    public function deleteRoomTypeImage(RoomImage $image): RedirectResponse
    {
        $roomType = $image->roomType;
        $this->authorizeHotel($roomType->hotel);

        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();

        return back()->with('success', 'Photo deleted.');
    }

    public function deleteRoomTypeImages(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'image_ids'   => ['required', 'array', 'min:1'],
            'image_ids.*' => ['integer', 'exists:room_images,id'],
        ]);

        $images = RoomImage::whereIn('id', $data['image_ids'])->get();

        foreach ($images as $image) {
            $this->authorizeHotel($image->roomType->hotel);

            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }

            $image->delete();
        }

        return back()->with('success', $images->count() . ' photo(s) deleted.');
    }

    // ── Video management ──────────────────────────────────────────────────────

    public function storeVideo(Request $request, Hotel $hotel): RedirectResponse
    {
        $this->authorizeHotel($hotel);

        $data = $request->validate([
            'title'     => ['nullable', 'string', 'max:150'],
            'video'     => ['required_without:video_url', 'nullable', 'file', 'mimes:mp4,mov,webm,avi', 'max:51200'],
            'video_url' => ['required_without:video', 'nullable', 'url', 'max:500'],
        ]);

        if ($request->hasFile('video')) {
            $this->hotelService->uploadVideo($hotel, $request->file('video'), $data['title'] ?? null);
        } else {
            $this->hotelService->addVideoLink($hotel, $data['video_url'], $data['title'] ?? null);
        }

        return back()->with('success', 'Video added successfully.');
    }

    public function deleteVideo(HotelVideo $video): RedirectResponse
    {
        $hotel = $video->hotel;
        $this->authorizeHotel($hotel);

        $this->hotelService->deleteVideo($video);

        return back()->with('success', 'Video deleted.');
    }

    // ── Online booking toggle ─────────────────────────────────────────────────

    public function toggleOnlineBooking(Hotel $hotel): RedirectResponse
    {
        $this->authorizeHotel($hotel);

        $hotel->update(['online_booking_enabled' => ! $hotel->online_booking_enabled]);

        $label = $hotel->online_booking_enabled ? 'enabled' : 'disabled';

        return back()->with('success', "Online booking {$label} successfully.");
    }

    // ── Payment methods ───────────────────────────────────────────────────────

    public function updatePaymentMethods(Request $request, Hotel $hotel): RedirectResponse
    {
        $this->authorizeHotel($hotel);

        $data = $request->validate([
            'payment_methods'   => ['required', 'array', 'min:1'],
            'payment_methods.*' => ['in:' . implode(',', Hotel::ALL_PAYMENT_METHODS)],
        ], [
            'payment_methods.min' => 'At least one payment method must be enabled.',
        ]);

        $hotel->update(['payment_methods' => $data['payment_methods']]);

        return back()->with('success', 'Payment methods updated successfully.');
    }

    // ── Manual payment fallback ───────────────────────────────────────────────

    public function updateManualPayment(Request $request, Hotel $hotel): RedirectResponse
    {
        $this->authorizeHotel($hotel);

        $data = $request->validate([
            'manual_payment_numbers'          => ['nullable', 'array'],
            'manual_payment_numbers.*.number' => ['nullable', 'string', 'max:20'],
            'manual_payment_numbers.*.name'   => ['nullable', 'string', 'max:100'],
        ]);

        $numbers = collect($data['manual_payment_numbers'] ?? [])
            ->filter(fn ($entry) => filled($entry['number'] ?? null))
            ->map(fn ($entry) => ['number' => $entry['number'], 'name' => $entry['name'] ?? ''])
            ->all();

        $hotel->update([
            'manual_payment_enabled' => $request->boolean('manual_payment_enabled'),
            'manual_payment_numbers' => $numbers,
        ]);

        return back()->with('success', 'Manual payment settings updated successfully.');
    }

    // ── Guard ─────────────────────────────────────────────────────────────────

    private function authorizeHotel(Hotel $hotel): void
    {
        abort_unless(
            auth()->user()->isSuperAdmin() || $hotel->isOwnedBy(auth()->user()),
            403,
            'You do not have permission to manage this hotel.'
        );
    }
}
