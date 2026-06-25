<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'marketing_opt_in',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'marketing_opt_in'  => 'boolean',
        ];
    }

    // ── RBAC ──────────────────────────────────────────────────────────────────

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles->flatMap->permissions->contains('name', $permission);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isHotelOwner(): bool
    {
        return $this->hasRole('hotel-owner');
    }

    public function isReceptionist(): bool
    {
        return $this->hasRole('receptionist');
    }

    // ── Hotel relations ───────────────────────────────────────────────────────

    /** Hotels this user owns (hotel-owner role) */
    public function ownedHotels()
    {
        return $this->hasMany(Hotel::class, 'owner_id');
    }

    // ── Booking domain ────────────────────────────────────────────────────────

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBookings()
    {
        return $this->hasMany(Booking::class)->active();
    }

    public function upcomingBookings()
    {
        return $this->hasMany(Booking::class)->upcoming();
    }

    // ── Favorites ─────────────────────────────────────────────────────────────

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteHotels()
    {
        return $this->belongsToMany(Hotel::class, 'favorites');
    }

    public function hasFavorited(int $hotelId): bool
    {
        return $this->favorites()->where('hotel_id', $hotelId)->exists();
    }

    // ── Reviews ───────────────────────────────────────────────────────────────

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ── Legacy e-commerce (kept until Phase 11 cleanup) ───────────────────────

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }
}
