<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'category'];

    // category values: general|room|bathroom|outdoor|dining|wellness|business

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'hotel_amenity');
    }

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'room_amenity');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
