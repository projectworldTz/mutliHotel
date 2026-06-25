<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository
{
    // ── Single lookups ────────────────────────────────────────────────────────

    public function find(int $id): ?Booking
    {
        return Booking::with(['hotel', 'rooms.room', 'rooms.roomType', 'user', 'payment', 'invoice'])
            ->find($id);
    }

    public function findByNumber(string $number): ?Booking
    {
        return Booking::with(['hotel', 'rooms.room', 'rooms.roomType', 'user', 'payment', 'invoice'])
            ->where('booking_number', $number)
            ->first();
    }

    // ── User-scoped listing ───────────────────────────────────────────────────

    public function forUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Booking::forUser($user->id)
            ->with(['hotel.featuredImage', 'rooms.roomType'])
            ->latest()
            ->paginate($perPage);
    }

    public function upcomingForUser(User $user, int $limit = 5): Collection
    {
        return Booking::forUser($user->id)
            ->upcoming()
            ->with(['hotel.featuredImage'])
            ->limit($limit)
            ->get();
    }

    // ── Hotel-scoped listing ──────────────────────────────────────────────────

    public function forHotel(Hotel $hotel, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Booking::forHotel($hotel->id)->with(['user', 'rooms.roomType']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('check_in', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('check_out', '<=', $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('booking_number', 'like', "%{$term}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")
                                                      ->orWhere('email', 'like', "%{$term}%"));
            });
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function recentForHotel(Hotel $hotel, int $limit = 5): Collection
    {
        return Booking::forHotel($hotel->id)
            ->with(['user'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    // ── Admin listing ─────────────────────────────────────────────────────────

    public function allPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Booking::with(['hotel', 'user'])->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['hotel_id'])) {
            $query->where('hotel_id', $filters['hotel_id']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('booking_number', 'like', "%{$term}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"));
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    // ── Stats / analytics ─────────────────────────────────────────────────────

    public function platformStats(): array
    {
        return [
            'total'        => Booking::count(),
            'pending'      => Booking::pending()->count(),
            'confirmed'    => Booking::confirmed()->count(),
            'checked_in'   => Booking::where('status', Booking::STATUS_CHECKED_IN)->count(),
            'cancelled'    => Booking::where('status', Booking::STATUS_CANCELLED)->count(),
            'total_revenue' => Booking::whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT,
            ])->sum('grand_total'),
        ];
    }

    public function hotelStats(Hotel $hotel): array
    {
        $base = Booking::forHotel($hotel->id);

        return [
            'total'        => (clone $base)->count(),
            'pending'      => (clone $base)->pending()->count(),
            'confirmed'    => (clone $base)->confirmed()->count(),
            'checked_in'   => (clone $base)->where('status', Booking::STATUS_CHECKED_IN)->count(),
            'cancelled'    => (clone $base)->where('status', Booking::STATUS_CANCELLED)->count(),
            'total_revenue' => (clone $base)->whereIn('status', [
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CHECKED_IN,
                Booking::STATUS_CHECKED_OUT,
            ])->sum('grand_total'),
        ];
    }

    public function revenueByMonth(int $months = 12): array
    {
        return Booking::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(grand_total) as total')
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->toArray();
    }
}
