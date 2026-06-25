<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonalPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'room_type_id',
        'name',
        'start_date',
        'end_date',
        'modifier_type',
        'modifier_value',
        'min_stay_nights',
        'priority',
        'active',
    ];

    protected $casts = [
        'start_date'      => 'date',
        'end_date'        => 'date',
        'modifier_value'  => 'decimal:2',
        'min_stay_nights' => 'integer',
        'priority'        => 'integer',
        'active'          => 'boolean',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForDate($query, string $date)
    {
        return $query->where('start_date', '<=', $date)->where('end_date', '>=', $date);
    }

    /**
     * Apply this rule's modifier to a base price and return the resulting price.
     */
    public function applyTo(float $basePrice): float
    {
        return $this->modifier_type === 'percentage'
            ? $basePrice * (1 + (float) $this->modifier_value / 100)
            : $basePrice + (float) $this->modifier_value;
    }
}
