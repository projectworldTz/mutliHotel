<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckAvailabilityRequest;
use App\Models\Hotel;
use App\Models\HotelVisit;
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

        // Tenant isolation: block navigation to a different hotel's page
        if (app()->bound('current_hotel') && app('current_hotel')->id !== $hotel->id) {
            return redirect()->route('hotels.show', app('current_hotel'));
        }

        // Remember which hotel page the guest is on so login/register/logout
        // can return them here even without subdomain-based tenant routing.
        request()->session()->put('viewing_hotel', $hotel->slug);

        $this->recordVisit($hotel);

        // A guest looking at one hotel's page shouldn't see nav links back to
        // the platform's own marketing site or to other hotels — treat this
        // as tenant mode for the layout regardless of subdomain vs path access.
        view()->share('tenantMode', true);
        view()->share('currentHotel', $hotel);

        $hotel->loadMissing([
            'images', 'videos', 'amenities', 'roomTypes.images', 'roomTypes.amenities',
            'approvedReviews.user', 'category',
        ]);

        return view('hotels.public', compact('hotel'));
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

        $formatType = fn ($r, bool $isAvailable) => [
            'id'              => $r['room_type']->id,
            'name'            => $r['room_type']->name,
            'slug'            => $r['room_type']->slug,
            'max_guests'      => $r['room_type']->max_guests,
            'bed_type'        => $r['room_type']->bed_type,
            'size_sqm'        => $r['room_type']->size_sqm,
            'available'       => $isAvailable,
            'available_count' => $r['available_count'],
            'nightly_rate'    => $isAvailable ? $r['pricing']['nightly_rate'] : null,
            'nights'          => $isAvailable ? $r['pricing']['nights']        : null,
            'total'           => $isAvailable ? $r['pricing']['subtotal']      : null,
            'image'           => $r['room_type']->images->first()?->url,
            'next_available'  => $r['next_available'],
            'reason'          => $r['reason'],
        ];

        return response()->json([
            'available'          => ! empty($results['available']),
            'check_in'           => $request->check_in,
            'check_out'          => $request->check_out,
            'guests'             => $request->guests,
            'room_types'         => array_map(fn ($r) => $formatType($r, true),  $results['available']),
            'unavailable_types'  => array_map(fn ($r) => $formatType($r, false), $results['unavailable']),
        ]);
    }

    /**
     * Log one visit per browser session per hotel per day, for the owner's
     * "visits per day" analytics. Skips the hotel's own owner so their
     * dashboard checks don't inflate their own traffic numbers.
     */
    private function recordVisit(Hotel $hotel): void
    {
        if (auth()->check() && $hotel->isOwnedBy(auth()->user())) {
            return;
        }

        HotelVisit::firstOrCreate([
            'hotel_id'    => $hotel->id,
            'visitor_key' => request()->session()->getId(),
            'visited_on'  => now()->toDateString(),
        ]);
    }
}
