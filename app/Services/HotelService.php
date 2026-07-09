<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\HotelVideo;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\HotelRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HotelService
{
    public function __construct(private HotelRepository $repository) {}

    // ── Public browsing ───────────────────────────────────────────────────────

    public function search(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return $this->repository->search($filters, $perPage);
    }

    public function findBySlug(string $slug): Hotel
    {
        $hotel = $this->repository->findBySlug($slug);

        abort_if(is_null($hotel), 404, 'Hotel not found.');

        return $hotel;
    }

    public function getFeatured(int $limit = 6): Collection
    {
        return $this->repository->featured($limit);
    }

    public function getCategories(): Collection
    {
        return $this->repository->categories();
    }

    public function getRelated(Hotel $hotel, int $limit = 4): Collection
    {
        return $this->repository->related($hotel, $limit);
    }

    // ── Owner management ──────────────────────────────────────────────────────

    public function getForOwner(User $owner, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->forOwner($owner, $perPage);
    }

    public function allForOwner(User $owner): Collection
    {
        return $this->repository->allForOwner($owner);
    }

    public function allForAdmin(array $filters = [], int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repository->allForAdmin($filters, $perPage);
    }

    // ── CRUD ──────────────────────────────────────────────────────────────────

    public function create(array $data, User $owner): Hotel
    {
        $data['owner_id']        = $owner->id;
        $data['slug']            = $this->uniqueSlug($data['name']);
        $data['status']          = 'pending'; // always starts as pending until admin approves
        $data['commission_rate'] = (float) Setting::get('default_commission_rate', 10);

        $hotel = Hotel::create($data);

        if (! empty($data['amenity_ids'])) {
            $hotel->amenities()->sync($data['amenity_ids']);
        }

        event(new \App\Events\HotelSubmitted($hotel));

        return $hotel;
    }

    public function update(Hotel $hotel, array $data): Hotel
    {
        if (isset($data['name']) && $data['name'] !== $hotel->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $hotel->id);
        }

        $hotel->update($data);

        if (isset($data['amenity_ids'])) {
            $hotel->amenities()->sync($data['amenity_ids']);
        }

        return $hotel->fresh();
    }

    public function delete(Hotel $hotel): void
    {
        $hotel->delete(); // soft delete preserves booking history
    }

    // ── Image management ──────────────────────────────────────────────────────

    public function uploadImage(Hotel $hotel, UploadedFile $file, bool $featured = false): HotelImage
    {
        $path = $file->store("hotels/{$hotel->id}/gallery", 'public');

        if ($featured) {
            $hotel->images()->update(['is_featured' => false]);
        }

        return $hotel->images()->create([
            'path'        => $path,
            'url'         => Storage::disk('public')->url($path),
            'is_featured' => $featured || $hotel->images()->count() === 0,
            'sort_order'  => $hotel->images()->max('sort_order') + 1,
        ]);
    }

    public function deleteImage(HotelImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }

    public function reorderImages(Hotel $hotel, array $orderedIds): void
    {
        foreach ($orderedIds as $position => $imageId) {
            $hotel->images()->where('id', $imageId)->update(['sort_order' => $position]);
        }
    }

    // ── Video management ──────────────────────────────────────────────────────

    public function uploadVideo(Hotel $hotel, UploadedFile $file, ?string $title = null): HotelVideo
    {
        $path = $file->store("hotels/{$hotel->id}/videos", 'public');

        return $hotel->videos()->create([
            'title'      => $title,
            'source'     => 'upload',
            'path'       => $path,
            'url'        => Storage::disk('public')->url($path),
            'sort_order' => $hotel->videos()->max('sort_order') + 1,
        ]);
    }

    public function addVideoLink(Hotel $hotel, string $url, ?string $title = null): HotelVideo
    {
        return $hotel->videos()->create([
            'title'      => $title,
            'source'     => 'embed',
            'url'        => $url,
            'sort_order' => $hotel->videos()->max('sort_order') + 1,
        ]);
    }

    public function deleteVideo(HotelVideo $video): void
    {
        if ($video->isUpload() && $video->path) {
            Storage::disk('public')->delete($video->path);
        }

        $video->delete();
    }

    // ── Room type management ──────────────────────────────────────────────────

    public function createRoomType(Hotel $hotel, array $data): RoomType
    {
        $data['hotel_id'] = $hotel->id;
        $data['slug']     = Str::slug($data['name']);

        $roomType = RoomType::create($data);

        if (! empty($data['amenity_ids'])) {
            $roomType->amenities()->sync($data['amenity_ids']);
        }

        return $roomType;
    }

    public function updateRoomType(RoomType $roomType, array $data): RoomType
    {
        $roomType->update($data);

        if (isset($data['amenity_ids'])) {
            $roomType->amenities()->sync($data['amenity_ids']);
        }

        return $roomType->fresh();
    }

    /** @throws \RuntimeException when the room type has existing bookings */
    public function deleteRoomType(RoomType $roomType): void
    {
        if ($roomType->bookingRooms()->exists()) {
            throw new \RuntimeException(
                'This room type has existing bookings and cannot be deleted. Mark it inactive instead.'
            );
        }

        $roomType->delete();
    }

    // ── Room management ───────────────────────────────────────────────────────

    public function createRoom(Hotel $hotel, RoomType $roomType, array $data): Room
    {
        return Room::create([
            'hotel_id'     => $hotel->id,
            'room_type_id' => $roomType->id,
            'room_number'  => $data['room_number'],
            'floor'        => $data['floor'] ?? null,
            'status'       => $data['status'] ?? 'available',
        ]);
    }

    public function updateRoom(Room $room, array $data): Room
    {
        $room->update($data);

        return $room->fresh();
    }

    /** @throws \RuntimeException when the room has existing bookings */
    public function deleteRoom(Room $room): void
    {
        if ($room->bookingRooms()->exists()) {
            throw new \RuntimeException(
                'This room has existing bookings and cannot be deleted. Mark it out of service instead.'
            );
        }

        $room->delete();
    }

    // ── Admin actions ─────────────────────────────────────────────────────────

    public function approve(Hotel $hotel): Hotel
    {
        $hotel->update(['status' => 'active']);

        return $hotel;
    }

    public function suspend(Hotel $hotel, string $reason = ''): Hotel
    {
        $hotel->update(['status' => 'suspended']);

        return $hotel;
    }

    public function toggleFeatured(Hotel $hotel): Hotel
    {
        $hotel->update(['featured' => ! $hotel->featured]);

        return $hotel;
    }

    public function stats(): array
    {
        return $this->repository->stats();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function uniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 1;

        while (
            Hotel::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
