<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckAvailabilityRequest;
use App\Models\Hotel;
use App\Services\AvailabilityService;
use App\Services\HotelService;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function __construct(
        private HotelService       $hotelService,
        private AvailabilityService $availabilityService,
    ) {}

    /**
     * Hotel search / listing page.
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'city', 'country', 'category_id',
            'star_rating', 'min_price', 'max_price', 'amenities', 'sort',
        ]);

        $hotels     = $this->hotelService->search($filters, 12);
        $categories = $this->hotelService->getCategories();

        return view('hotels.index', compact('hotels', 'categories', 'filters'));
    }

    /**
     * Hotel detail page.
     */
    public function show(Hotel $hotel)
    {
        abort_if($hotel->status !== 'active', 404);

        $hotel->loadMissing([
            'images', 'amenities', 'roomTypes.images', 'roomTypes.amenities',
            'approvedReviews.user', 'category',
        ]);

        $related = $this->hotelService->getRelated($hotel, 4);

        return view('hotels.show', compact('hotel', 'related'));
    }

    /**
     * AJAX endpoint: check availability for all room types of a hotel.
     * Called by the React availability checker component.
     */
    public function availability(Hotel $hotel, CheckAvailabilityRequest $request)
    {
        abort_if($hotel->status !== 'active', 404);

        $results = $this->availabilityService->availableRoomTypes(
            $hotel,
            $request->check_in,
            $request->check_out,
            (int) $request->guests
        );

        return response()->json([
            'available'  => ! empty($results),
            'check_in'   => $request->check_in,
            'check_out'  => $request->check_out,
            'guests'     => $request->guests,
            'room_types' => array_map(fn ($r) => [
                'id'              => $r['room_type']->id,
                'name'            => $r['room_type']->name,
                'slug'            => $r['room_type']->slug,
                'max_guests'      => $r['room_type']->max_guests,
                'bed_type'        => $r['room_type']->bed_type,
                'size_sqm'        => $r['room_type']->size_sqm,
                'available_count' => $r['available_count'],
                'nightly_rate'    => $r['pricing']['nightly_rate'],
                'nights'          => $r['pricing']['nights'],
                'total'           => $r['pricing']['subtotal'],
                'image_url'       => $r['room_type']->featured_image_url,
            ], $results),
        ]);
    }
}
