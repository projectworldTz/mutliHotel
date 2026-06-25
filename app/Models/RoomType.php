<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'slug',
        'description',
        'base_price',
        'max_guests',
        'bed_type',
        'beds_count',
        'size_sqm',
        'view_type',
        'smoking',
        'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'max_guests' => 'integer',
        'beds_count' => 'integer',
        'size_sqm'   => 'decimal:2',
        'smoking'    => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class)->orderBy('sort_order');
    }

    public function featuredImage()
    {
        return $this->hasOne(RoomImage::class)->where('is_featured', true)->orderBy('sort_order');
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_amenity');
    }

    public function bookingRooms()
    {
        return $this->hasMany(BookingRoom::class);
    }

    public function seasonalPrices()
    {
        return $this->hasMany(SeasonalPrice::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForGuests($query, int $guests)
    {
        return $query->where('max_guests', '>=', $guests);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featuredImage?->url ?? $this->images()->first()?->url;
    }

    public function getAvailableRoomsCountAttribute(): int
    {
        return $this->rooms()->where('status', 'available')->count();
    }

    // ── Pricing helpers ───────────────────────────────────────────────────────

    /**
     * Compute the effective nightly price for a given date, applying any
     * active seasonal price override. Falls back to base_price.
     */
    public function priceForDate(\Carbon\Carbon $date): float
    {
        $seasonal = $this->seasonalPrices()
            ->where('active', true)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->orderByDesc('priority')
            ->first();

        if (! $seasonal) {
            return (float) $this->base_price;
        }

        return $seasonal->modifier_type === 'percentage'
            ? (float) $this->base_price * (1 + $seasonal->modifier_value / 100)
            : (float) $this->base_price + (float) $seasonal->modifier_value;
    }

    /**
     * Calculate the total price for a stay period (check-in to check-out).
     * Returns ['nightly_rate' => avg, 'nights' => n, 'total' => total].
     */
    public function priceForStay(\Carbon\Carbon $checkIn, \Carbon\Carbon $checkOut): array
    {
        $nights = $checkIn->diffInDays($checkOut);
        $total  = 0.0;

        for ($i = 0; $i < $nights; $i++) {
            $total += $this->priceForDate($checkIn->copy()->addDays($i));
        }

        return [
            'nightly_rate' => $nights > 0 ? round($total / $nights, 2) : (float) $this->base_price,
            'nights'       => $nights,
            'total'        => round($total, 2),
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
