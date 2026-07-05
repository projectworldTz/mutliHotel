<?php

namespace App\Events;

use App\Models\Hotel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class HotelSubmitted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Hotel $hotel) {}
}
