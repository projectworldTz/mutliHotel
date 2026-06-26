<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::with(['hotel'])
            ->latest()
            ->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $hotels = Hotel::where('status', 'active')->orderBy('name')->get();

        return view('admin.coupons.create', compact('hotels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'               => ['nullable', 'string', 'max:50', 'unique:coupons,code'],
            'type'               => ['required', 'in:percentage,fixed'],
            'value'              => ['required', 'numeric', 'min:0.01'],
            'hotel_id'           => ['nullable', 'exists:hotels,id'],
            'min_booking_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses'           => ['nullable', 'integer', 'min:1'],
            'expires_at'         => ['nullable', 'date', 'after:today'],
        ]);

        if ($data['type'] === 'percentage' && $data['value'] > 100) {
            return back()->withInput()->withErrors(['value' => 'Percentage cannot exceed 100.']);
        }

        $data['code']   = strtoupper($data['code'] ?? self::generateCode($data['hotel_id'] ?? null));
        $data['active'] = true;
        $data['uses']   = 0;

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', "Coupon {$data['code']} created successfully.");
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['active' => ! $coupon->active]);

        $state = $coupon->active ? 'activated' : 'deactivated';

        return back()->with('success', "Coupon {$coupon->code} {$state}.");
    }

    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();

        return back()->with('success', "Coupon {$code} deleted.");
    }

    public static function generateCode(?int $hotelId = null): string
    {
        if ($hotelId) {
            $hotel  = Hotel::find($hotelId);
            $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $hotel->name ?? 'HTL'), 0, 4));
        } else {
            $prefix = 'STAY';
        }

        do {
            $code = $prefix . '-' . strtoupper(Str::random(5));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}
