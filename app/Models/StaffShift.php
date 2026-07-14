<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffShift extends Model
{
    protected $fillable = [
        'hotel_id', 'user_id', 'shift_date', 'start_time', 'end_time',
        'role', 'notes', 'created_by',
    ];

    protected $casts = [
        'shift_date' => 'date',
    ];

    public function hotel(): BelongsTo  { return $this->belongsTo(Hotel::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeForHotel($query, int $hotelId) { return $query->where('hotel_id', $hotelId); }
    public function scopeForUser($query, int $userId)   { return $query->where('user_id', $userId); }
    public function scopeUpcoming($query)               { return $query->where('shift_date', '>=', now()->toDateString()); }

    public function getTimeRangeAttribute(): string
    {
        return substr($this->start_time, 0, 5) . '–' . substr($this->end_time, 0, 5);
    }
}
