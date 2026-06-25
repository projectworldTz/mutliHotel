<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'room_id',
        'room_type_id',
        'check_in',
        'check_out',
        'nightly_rate',
        'nights',
        'sub_total',
    ];

    protected $casts = [
        'check_in'     => 'date',
        'check_out'    => 'date',
        'nightly_rate' => 'decimal:2',
        'nights'       => 'integer',
        'sub_total'    => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
