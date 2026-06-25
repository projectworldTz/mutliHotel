<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'hotel_category_id',
        'owner_id',
        'name',
        'slug',
        'description',
        'short_description',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'star_rating',
        'phone',
        'email',
        'website',
        'check_in_time',
        'check_out_time',
        'total_rooms',
        'commission_rate',
        'status',
        'featured',
        'cancellation_policy',
    ];

    protected $casts = [
        'star_rating'     => 'integer',
        'total_rooms'     => 'integer',
        'commission_rate' => 'decimal:2',
        'featured'        => 'boolean',
        'latitude'        => 'float',
        'longitude'       => 'float',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(HotelCategory::class, 'hotel_category_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(HotelImage::class)->orderBy('sort_order');
    }

    public function featuredImage()
    {
        return $this->hasOne(HotelImage::class)->where('is_featured', true)->orderBy('sort_order');
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'hotel_amenity');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function approvedReviews()
    {
        return $this->hasMany(Review::class)->where('approved', true);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
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

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', 'like', "%{$city}%");
    }

    public function scopeInCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('hotel_category_id', $categoryId);
    }

    public function scopeByStars($query, int $stars)
    {
        return $query->where('star_rating', $stars);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('city', 'like', "%{$term}%")
              ->orWhere('country', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getAverageRatingAttribute(): float
    {
        return round((float) ($this->approvedReviews()->avg('rating') ?? 0), 1);
    }

    public function getReviewCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featuredImage?->url ?? $this->images()->first()?->url;
    }

    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'active'    => ['label' => 'Active',    'color' => 'green'],
            'pending'   => ['label' => 'Pending',   'color' => 'yellow'],
            'suspended' => ['label' => 'Suspended', 'color' => 'red'],
            default     => ['label' => 'Inactive',  'color' => 'gray'],
        };
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }
}
