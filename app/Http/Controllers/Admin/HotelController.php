<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Models\Hotel;
use App\Services\HotelService;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function __construct(private HotelService $hotelService) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search', 'city', 'star_rating']);
        $hotels  = $this->hotelService->allForAdmin($filters);

        return view('admin.hotels.index', compact('hotels', 'filters'));
    }

    public function show(Hotel $hotel)
    {
        $hotel->loadMissing([
            'owner', 'images', 'amenities', 'roomTypes.images',
            'category', 'approvedReviews',
        ]);

        $stats = \App\Models\Booking::where('hotel_id', $hotel->id)
            ->selectRaw('COUNT(*) as total, SUM(grand_total) as revenue')
            ->first();

        return view('admin.hotels.show', compact('hotel', 'stats'));
    }

    public function approve(Hotel $hotel)
    {
        $this->hotelService->approve($hotel);

        return back()->with('success', "\"{$hotel->name}\" has been approved and is now live.");
    }

    public function suspend(Hotel $hotel, Request $request)
    {
        $this->hotelService->suspend($hotel, $request->input('reason', ''));

        return back()->with('success', "\"{$hotel->name}\" has been suspended.");
    }

    public function toggleFeatured(Hotel $hotel)
    {
        $this->hotelService->toggleFeatured($hotel);
        $state = $hotel->fresh()->featured ? 'featured' : 'unfeatured';

        return back()->with('success', "\"{$hotel->name}\" is now {$state}.");
    }

    public function destroy(Hotel $hotel)
    {
        $this->hotelService->delete($hotel);

        return redirect()->route('admin.hotels.index')
            ->with('success', "Hotel \"{$hotel->name}\" deleted.");
    }
}
