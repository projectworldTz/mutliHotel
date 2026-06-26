<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Models\ReservationCartItem;
use App\Models\RoomType;
use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PricingService $pricingService,
    ) {}

    public function index()
    {
        $cart = $this->bookingService->getCart(auth()->user());

        return response()->json([
            'items'      => $cart->items,
            'subtotal'   => $cart->sub_total,
            'count'      => $cart->item_count,
            'expires_at' => $cart->expires_at?->toIso8601String(),
        ]);
    }

    public function store(AddToCartRequest $request)
    {
        $roomType = RoomType::findOrFail($request->room_type_id);

        try {
            $this->bookingService->addToCart(
                auth()->user(),
                $roomType,
                $request->check_in,
                $request->check_out,
                (int) $request->guests
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $cart = $this->bookingService->getCart(auth()->user());

        return response()->json([
            'success'  => true,
            'message'  => 'Room added to your reservation.',
            'count'    => $cart->item_count,
            'subtotal' => $cart->sub_total,
        ], 201);
    }

    public function destroy(ReservationCartItem $item)
    {
        abort_unless($item->cart->user_id === auth()->id(), 403);

        $this->bookingService->removeFromCart($item);

        $cart = $this->bookingService->getCart(auth()->user());

        return response()->json([
            'success'  => true,
            'count'    => $cart->item_count,
            'subtotal' => $cart->sub_total,
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $cart    = $this->bookingService->getCart(auth()->user());
        $hotelId = $cart->items->first()?->roomType?->hotel_id;

        return response()->json(
            $this->bookingService->applyCouponPreview(auth()->user(), $request->code, $hotelId)
        );
    }

    public function preview(Request $request)
    {
        $cart   = $this->bookingService->getCart(auth()->user());
        $coupon = null;

        if ($request->filled('coupon_code')) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))
                ->valid()
                ->first();
        }

        return response()->json(
            $this->pricingService->calculateOrderTotal((float) $cart->sub_total, $coupon)
        );
    }
}
