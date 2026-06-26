<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\HotelService;
use Illuminate\Http\Request;

class HotelApiController extends Controller
{
    public function __construct(private HotelService $hotelService) {}

    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'city', 'country', 'category_id',
            'star_rating', 'min_price', 'max_price', 'amenities', 'sort',
        ]);

        $hotels = $this->hotelService->search($filters, (int) $request->input('per_page', 12));

        return response()->json([
            'data'  => $hotels->items(),
            'meta'  => [
                'current_page' => $hotels->currentPage(),
                'last_page'    => $hotels->lastPage(),
                'per_page'     => $hotels->perPage(),
                'total'        => $hotels->total(),
            ],
        ]);
    }

    public function show(Hotel $hotel)
    {
        abort_if($hotel->status !== 'active', 404);

        $hotel->loadMissing([
            'images', 'amenities', 'roomTypes.images', 'roomTypes.amenities', 'category',
        ]);

        return response()->json(['data' => $hotel]);
    }

    public function roomTypes(Hotel $hotel)
    {
        abort_if($hotel->status !== 'active', 404);

        $roomTypes = $hotel->roomTypes()->with(['images', 'amenities'])->get();

        return response()->json(['data' => $roomTypes]);
    }

    public function featured()
    {
        $hotels = $this->hotelService->getFeatured(8);

        return response()->json(['data' => $hotels]);
    }
}
