<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservationCartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_cart_id',
        'room_id',
        'room_type_id',
        'check_in',
        'check_out',
        'guests',
        'nightly_rate',
        'nights',
        'sub_total',
    ];

    protected $casts = [
        'check_in'     => 'date',
        'check_out'    => 'date',
        'guests'       => 'integer',
        'nightly_rate' => 'decimal:2',
        'nights'       => 'integer',
        'sub_total'    => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(ReservationCart::class, 'reservation_cart_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function getHotelAttribute(): ?Hotel
    {
        return $this->room?->hotel;
    }

    /**
     * Re-validate that the room is still available and reprice if seasonal prices changed.
     */
    public function refreshPricing(): void
    {
        $roomType = $this->roomType;
        $pricing  = $roomType->priceForStay(
            \Carbon\Carbon::parse($this->check_in),
            \Carbon\Carbon::parse($this->check_out)
        );

        $this->update([
            'nightly_rate' => $pricing['nightly_rate'],
            'nights'       => $pricing['nights'],
            'sub_total'    => $pricing['total'],
        ]);
    }
}
