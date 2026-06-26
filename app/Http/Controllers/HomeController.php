<?php

namespace App\Http\Controllers;

use App\Services\BookingService;
use App\Services\HotelService;

class HomeController extends Controller
{
    public function __construct(
        private HotelService   $hotelService,
        private BookingService $bookingService,
    ) {}

    public function index()
    {
        $featured   = $this->hotelService->getFeatured(8);
        $categories = $this->hotelService->getCategories();
        $stats      = [
            'hotels'   => $this->hotelService->stats(),
            'bookings' => $this->bookingService->platformStats(),
        ];

        return view('home', compact('featured', 'categories', 'stats'));
    }
}
