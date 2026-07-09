<?php

namespace App\Models;

use App\Enums\Feature;
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
        'payment_methods',
        'online_booking_enabled',
        'manual_payment_enabled',
        'manual_payment_numbers',
    ];

    protected $casts = [
        'star_rating'     => 'integer',
        'total_rooms'     => 'integer',
        'commission_rate' => 'decimal:2',
        'featured'         => 'boolean',
        'latitude'         => 'float',
        'longitude'        => 'float',
        'payment_methods'         => 'array',
        'online_booking_enabled'  => 'boolean',
        'manual_payment_enabled'  => 'boolean',
        'manual_payment_numbers'  => 'array',
    ];

    /** All payment method keys supported by the platform. */
    public const ALL_PAYMENT_METHODS = ['airtel_money', 'mpesa', 'halotel', 'mix_by_yas', 'dpo_card'];

    /** Mobile money keys only — used for the manual-payment-numbers fallback (card has no "number" to hand out). */
    public const MOBILE_MONEY_METHODS = ['airtel_money', 'mpesa', 'halotel', 'mix_by_yas'];

    /**
     * Return the enabled payment method keys for this hotel.
     * Falls back to all methods when the owner has not configured anything.
     */
    public function enabledPaymentMethods(): array
    {
        return $this->payment_methods ?: self::ALL_PAYMENT_METHODS;
    }

    /** Configured manual mobile-money numbers (['number' => ..., 'name' => ...]), keyed by method, empty ones dropped. */
    public function manualPaymentNumbers(): array
    {
        return array_filter(
            $this->manual_payment_numbers ?? [],
            fn ($entry) => filled($entry['number'] ?? null)
        );
    }

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

    public function videos()
    {
        return $this->hasMany(HotelVideo::class)->orderBy('sort_order');
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

    public function staff()
    {
        return $this->hasMany(HotelStaff::class);
    }

    public function activeStaff()
    {
        return $this->hasMany(HotelStaff::class)->where('active', true)->with('user');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function visits()
    {
        return $this->hasMany(HotelVisit::class);
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

    public function seasonalPrices()
    {
        return $this->hasMany(SeasonalPrice::class);
    }

    public function hotelFeatures()
    {
        return $this->hasMany(HotelFeature::class);
    }

    public function activeFeatures()
    {
        return $this->hasMany(HotelFeature::class)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
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

    /**
     * The hotel the current guest is scoped to, whether resolved from a
     * tenant subdomain or, in single-domain setups, the last hotel page visited.
     */
    public static function currentForGuest(): ?self
    {
        if (app()->bound('current_hotel')) {
            return app('current_hotel');
        }

        if ($slug = session('viewing_hotel')) {
            return static::where('slug', $slug)->where('status', 'active')->first();
        }

        // Single-hotel system: no other hotel to disambiguate between.
        return static::active()->first();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    public function hasFeature(Feature|string $feature): bool
    {
        $value = $feature instanceof Feature ? $feature->value : $feature;

        return $this->hotelFeatures()
            ->where('feature', $value)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    public function grantFeature(Feature|string $feature, int $grantedBy, ?string $expiresAt = null, ?string $notes = null): HotelFeature
    {
        $value = $feature instanceof Feature ? $feature->value : $feature;

        return $this->hotelFeatures()->updateOrCreate(
            ['feature' => $value],
            [
                'granted_by' => $grantedBy,
                'granted_at' => now(),
                'expires_at' => $expiresAt,
                'notes'      => $notes,
            ]
        );
    }

    public function revokeFeature(Feature|string $feature): int
    {
        $value = $feature instanceof Feature ? $feature->value : $feature;

        return $this->hotelFeatures()->where('feature', $value)->delete();
    }
}
