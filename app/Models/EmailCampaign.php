<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class EmailCampaign extends Model
{
    const AUDIENCE_PAST     = 'past_guests';
    const AUDIENCE_UPCOMING = 'upcoming_guests';
    const AUDIENCE_ALL      = 'all_guests';

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT   = 'sent';

    protected $fillable = [
        'hotel_id', 'subject', 'body', 'audience', 'status',
        'recipient_count', 'sent_at', 'created_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function hotel(): BelongsTo { return $this->belongsTo(Hotel::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeForHotel($query, int $hotelId) { return $query->where('hotel_id', $hotelId); }

    public function getAudienceLabelAttribute(): string
    {
        return match ($this->audience) {
            self::AUDIENCE_PAST     => 'Past Guests',
            self::AUDIENCE_UPCOMING => 'Upcoming Guests',
            default                 => 'All Guests',
        };
    }

    /**
     * Users this campaign should reach — past/upcoming guests of the hotel
     * (deduped), filtered to those who've opted in to marketing email.
     */
    public function targetUsers(): Collection
    {
        $hotelId = $this->hotel_id;

        $bookingsQuery = match ($this->audience) {
            self::AUDIENCE_PAST     => Booking::forHotel($hotelId)->pastGuests(),
            self::AUDIENCE_UPCOMING => Booking::forHotel($hotelId)->upcoming(),
            default                 => Booking::forHotel($hotelId)->where(
                fn ($q) => $q->pastGuests()->orWhere(
                    fn ($q2) => $q2->whereIn('status', [Booking::STATUS_PENDING, Booking::STATUS_CONFIRMED])
                                    ->where('check_in', '>=', now()->toDateString())
                )
            ),
        };

        return $bookingsQuery->with('user')
            ->get()
            ->pluck('user')
            ->filter(fn ($user) => $user && $user->marketing_opt_in)
            ->unique('id')
            ->values();
    }
}
