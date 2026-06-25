<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderApiController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->orders()->with('items.product')->latest()->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'billing_address_id' => 'required|exists:addresses,id',
            'shipping_address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|string|max:50',
        ]);

        $user = $request->user();
        $items = $user->cartItems()->with('product')->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $order = Order::create([
            'user_id' => $user->id,
            'billing_address_id' => $request->billing_address_id,
            'shipping_address_id' => $request->shipping_address_id,
            'status' => 'pending',
            'sub_total' => $items->sum(fn ($item) => $item->price * $item->quantity),
            'shipping_total' => 0,
            'tax_total' => 0,
            'discount_total' => 0,
            'grand_total' => $items->sum(fn ($item) => $item->price * $item->quantity),
        ]);

        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->price * $item->quantity,
            ]);
        }

        $user->cartItems()->delete();

        return response()->json($order->load('items.product'), 201);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== request()->user()->id) {
            abort(403);
        }

        return response()->json($order->load('items.product', 'billingAddress', 'shippingAddress'));
    }
}
