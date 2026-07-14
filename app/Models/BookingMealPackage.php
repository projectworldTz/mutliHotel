<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingMealPackage extends Model
{
    protected $fillable = [
        'booking_id', 'meal_package_id',
        'name', 'pricing_type', 'unit_price', 'quantity', 'sub_total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity'   => 'integer',
        'sub_total'  => 'decimal:2',
    ];

    public function booking(): BelongsTo     { return $this->belongsTo(Booking::class); }
    public function mealPackage(): BelongsTo { return $this->belongsTo(MealPackage::class); }
}
