<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\ReservationCartItem;
use App\Models\RoomType;
use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class BookingCartController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PricingService $pricingService,
    ) {}

    public function index()
    {
        $cart = $this->bookingService->getCart(auth()->user());

        return view('booking.cart', compact('cart'));
    }

    /**
     * Add a room type to the reservation cart.
     * Accepts JSON (React) or form POST.
     */
    public function store(AddToCartRequest $request)
    {
        $roomType = RoomType::findOrFail($request->room_type_id);

        try {
            $item = $this->bookingService->addToCart(
                auth()->user(),
                $roomType,
                $request->check_in,
                $request->check_out,
                (int) $request->guests
            );
        } catch (\RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['availability' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            $cart = $this->bookingService->getCart(auth()->user());
            return response()->json([
                'success'    => true,
                'message'    => 'Room added to your reservation.',
                'cart_count' => $cart->item_count,
                'subtotal'   => $cart->sub_total,
            ]);
        }

        return redirect()->route('booking.cart')->with('success', 'Room added to your reservation.');
    }

    public function destroy(ReservationCartItem $item)
    {
        abort_unless($item->cart->user_id === auth()->id(), 403);

        $this->bookingService->removeFromCart($item);

        if (request()->expectsJson()) {
            $cart = $this->bookingService->getCart(auth()->user());
            return response()->json([
                'success'  => true,
                'subtotal' => $cart->sub_total,
                'count'    => $cart->item_count,
            ]);
        }

        return back()->with('success', 'Item removed from cart.');
    }

    /**
     * Preview coupon discount against current cart (AJAX).
     */
    public function coupon(Request $request)
    {
        $request->validate(['code' => 'required|string|max:50']);

        $cart    = $this->bookingService->getCart(auth()->user());
        $hotelId = $cart->items->first()?->room?->hotel_id;

        $result = $this->bookingService->applyCouponPreview(
            auth()->user(),
            $request->code,
            $hotelId
        );

        return response()->json($result);
    }

    /**
     * Preview order total (tax + optional coupon). Used by checkout page.
     */
    public function preview(Request $request)
    {
        $cart    = $this->bookingService->getCart(auth()->user());
        $coupon  = null;

        if ($request->filled('coupon_code')) {
            $coupon = \App\Models\Coupon::where('code', strtoupper($request->coupon_code))->valid()->first();
        }

        $totals = $this->pricingService->calculateOrderTotal((float) $cart->sub_total, $coupon);

        return response()->json($totals);
    }
}
