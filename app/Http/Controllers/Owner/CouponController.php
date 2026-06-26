<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Hotel;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $coupons = Coupon::where('hotel_id', $hotel->id)
            ->latest()
            ->paginate(20);

        return view('owner.coupons.index', compact('hotel', 'coupons'));
    }

    public function create(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $roomTypes = $hotel->roomTypes()->active()->orderBy('name')->get();

        return view('owner.coupons.create', compact('hotel', 'roomTypes'));
    }

    public function store(Hotel $hotel, Request $request)
    {
        $this->authorizeHotel($hotel);

        $data = $request->validate([
            'code'               => ['nullable', 'string', 'max:50', 'unique:coupons,code'],
            'type'               => ['required', 'in:percentage,fixed'],
            'value'              => ['required', 'numeric', 'min:0.01'],
            'room_type_id'       => ['nullable', 'exists:room_types,id'],
            'min_booking_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses'           => ['nullable', 'integer', 'min:1'],
            'expires_at'         => ['nullable', 'date', 'after:today'],
        ]);

        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percentage cannot exceed 100.']);
        }

        $data['hotel_id'] = $hotel->id;
        $data['code']     = strtoupper($data['code'] ?? AdminCouponController::generateCode($hotel->id));
        $data['active']   = true;
        $data['uses']     = 0;

        Coupon::create($data);

        return redirect()->route('owner.hotels.coupons.index', $hotel)
            ->with('success', "Coupon {$data['code']} created successfully.");
    }

    public function toggle(Hotel $hotel, Coupon $coupon)
    {
        $this->authorizeHotel($hotel);
        abort_if($coupon->hotel_id !== $hotel->id, 403);

        $coupon->update(['active' => ! $coupon->active]);

        $state = $coupon->active ? 'activated' : 'deactivated';

        return back()->with('success', "Coupon {$coupon->code} {$state}.");
    }

    public function destroy(Hotel $hotel, Coupon $coupon)
    {
        $this->authorizeHotel($hotel);
        abort_if($coupon->hotel_id !== $hotel->id, 403);

        $code = $coupon->code;
        $coupon->delete();

        return back()->with('success', "Coupon {$code} deleted.");
    }

    private function authorizeHotel(Hotel $hotel): void
    {
        abort_unless(
            auth()->user()->isSuperAdmin() || $hotel->isOwnedBy(auth()->user()),
            403
        );
    }
}
