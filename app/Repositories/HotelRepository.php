<?php

namespace App\Repositories;

use App\Models\Hotel;
use App\Models\HotelCategory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HotelRepository
{
    // ── Single lookups ────────────────────────────────────────────────────────

    public function findBySlug(string $slug): ?Hotel
    {
        return Hotel::active()
            ->where('slug', $slug)
            ->with(['category', 'images', 'amenities', 'roomTypes.images', 'roomTypes.amenities', 'owner'])
            ->first();
    }

    public function findById(int $id): ?Hotel
    {
        return Hotel::with(['category', 'images', 'roomTypes', 'amenities', 'owner'])->find($id);
    }

    // ── Listing / search ──────────────────────────────────────────────────────

    /**
     * Full-featured search with filters.
     *
     * Supported filter keys:
     *   search, city, country, category_id, star_rating,
     *   min_price, max_price, amenities (array of IDs),
     *   sort (featured|price_asc|price_desc|rating|newest)
     */
    public function search(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Hotel::active()->with(['category', 'featuredImage', 'amenities']);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['city'])) {
            $query->inCity($filters['city']);
        }

        if (! empty($filters['country'])) {
            $query->inCountry($filters['country']);
        }

        if (! empty($filters['category_id'])) {
            $query->byCategory((int) $filters['category_id']);
        }

        if (! empty($filters['star_rating'])) {
            $query->byStars((int) $filters['star_rating']);
        }

        if (! empty($filters['min_price'])) {
            $query->whereHas('roomTypes', fn (Builder $q) =>
                $q->where('base_price', '>=', $filters['min_price'])
            );
        }

        if (! empty($filters['max_price'])) {
            $query->whereHas('roomTypes', fn (Builder $q) =>
                $q->where('base_price', '<=', $filters['max_price'])
            );
        }

        if (! empty($filters['amenities']) && is_array($filters['amenities'])) {
            foreach ($filters['amenities'] as $amenityId) {
                $query->whereHas('amenities', fn (Builder $q) =>
                    $q->where('amenities.id', $amenityId)
                );
            }
        }

        $this->applySort($query, $filters['sort'] ?? 'featured');

        return $query->paginate($perPage)->withQueryString();
    }

    public function allForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Hotel::with(['owner', 'category', 'featuredImage'])->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (! empty($filters['city'])) {
            $query->inCity($filters['city']);
        }

        if (! empty($filters['star_rating'])) {
            $query->byStars((int) $filters['star_rating']);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function featured(int $limit = 6): Collection
    {
        return Hotel::active()->featured()
            ->with(['featuredImage', 'amenities'])
            ->limit($limit)
            ->get();
    }

    public function forOwner(User $owner, int $perPage = 15): LengthAwarePaginator
    {
        return Hotel::where('owner_id', $owner->id)
            ->with(['category', 'featuredImage'])
            ->latest()
            ->paginate($perPage);
    }

    public function allForOwner(User $owner): Collection
    {
        return Hotel::where('owner_id', $owner->id)->get();
    }

    public function related(Hotel $hotel, int $limit = 4): Collection
    {
        return Hotel::active()
            ->where('hotel_category_id', $hotel->hotel_category_id)
            ->where('id', '!=', $hotel->id)
            ->inCity($hotel->city)
            ->with(['featuredImage'])
            ->limit($limit)
            ->get();
    }

    // ── Categories ────────────────────────────────────────────────────────────

    public function categories(): Collection
    {
        return HotelCategory::active()->orderBy('sort_order')->orderBy('name')->get();
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    public function stats(): array
    {
        return [
            'total'     => Hotel::count(),
            'active'    => Hotel::where('status', 'active')->count(),
            'pending'   => Hotel::where('status', 'pending')->count(),
            'suspended' => Hotel::where('status', 'suspended')->count(),
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc'  => $query->orderBy(
                \App\Models\RoomType::select('base_price')
                    ->whereColumn('hotel_id', 'hotels.id')
                    ->orderBy('base_price')
                    ->limit(1)
            ),
            'price_desc' => $query->orderByDesc(
                \App\Models\RoomType::select('base_price')
                    ->whereColumn('hotel_id', 'hotels.id')
                    ->orderBy('base_price')
                    ->limit(1)
            ),
            'rating'     => $query->orderByDesc('star_rating'),
            'newest'     => $query->latest(),
            default      => $query->orderByDesc('featured')->orderBy('name'),
        };
    }
}
