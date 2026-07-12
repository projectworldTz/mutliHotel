<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHotelRequest;
use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\HotelCategory;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HotelService;
use Illuminate\Http\Request;

class HotelController extends Controller
{
    public function __construct(
        private HotelService  $hotelService,
        private AuditService  $auditService,
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search', 'city', 'star_rating']);
        $hotels  = $this->hotelService->allForAdmin($filters);

        return view('admin.hotels.index', compact('hotels', 'filters'));
    }

    public function create()
    {
        $owners     = User::whereHas('roles', fn ($q) => $q->where('name', 'hotel-owner'))->orderBy('name')->get();
        $categories = HotelCategory::active()->orderBy('name')->get();
        $amenities  = Amenity::orderBy('category')->orderBy('name')->get();

        return view('admin.hotels.create', compact('owners', 'categories', 'amenities'));
    }

    public function store(StoreHotelRequest $request)
    {
        $data = $request->validated();

        $owner = User::findOrFail($data['owner_id']);
        abort_unless($owner->isHotelOwner(), 422, 'Selected user is not a hotel owner.');
        abort_unless($owner->canAddHotel(), 422, "{$owner->name} has reached their hotel limit.");

        unset($data['owner_id']);
        $hotel = $this->hotelService->create($data, $owner);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $this->hotelService->uploadImage($hotel, $file, $index === 0);
            }
        }

        $this->auditService->logHotelAction('hotel.created', $hotel, ['owner_id' => $owner->id]);

        return redirect()->route('admin.hotels.show', $hotel)
            ->with('success', "\"{$hotel->name}\" created for {$owner->name}.");
    }

    public function approve(Hotel $hotel)
    {
        $oldStatus = $hotel->status;
        $this->hotelService->approve($hotel);

        $this->auditService->logHotelAction('hotel.approved', $hotel, [
            'from_status' => $oldStatus,
            'to_status'   => 'active',
        ]);

        return back()->with('success', "\"{$hotel->name}\" has been approved and is now live.");
    }

    public function suspend(Hotel $hotel, Request $request)
    {
        $reason = $request->input('reason', '');
        $this->hotelService->suspend($hotel, $reason);

        $this->auditService->logHotelAction('hotel.suspended', $hotel, [
            'reason' => $reason,
        ]);

        return back()->with('success', "\"{$hotel->name}\" has been suspended.");
    }

    public function toggleFeatured(Hotel $hotel)
    {
        $this->hotelService->toggleFeatured($hotel);
        $featured = $hotel->fresh()->featured;

        $this->auditService->logHotelAction('hotel.featured', $hotel, [
            'featured' => $featured,
        ]);

        return back()->with('success', "\"{$hotel->name}\" is now " . ($featured ? 'featured' : 'unfeatured') . '.');
    }

    public function destroy(Hotel $hotel)
    {
        $name = $hotel->name;
        $this->auditService->logHotelAction('hotel.deleted', $hotel);
        $this->hotelService->delete($hotel);

        return redirect()->route('admin.hotels.index')
            ->with('success', "Hotel \"{$name}\" deleted.");
    }
}
