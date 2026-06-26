<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelStaff extends Model
{
    protected $table = 'hotel_staff';

    protected $fillable = ['hotel_id', 'user_id', 'position', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
