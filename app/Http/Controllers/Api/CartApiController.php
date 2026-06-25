<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartApiController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->cartItems()->with('product')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1',
        ]);

        $user = $request->user();
        $cart = $user->cart()->firstOrCreate([]);
        $item = $cart->items()->firstOrNew(['product_id' => $data['product_id']]);
        $item->quantity = $item->exists ? $item->quantity + ($data['quantity'] ?? 1) : ($data['quantity'] ?? 1);
        $item->price = $item->product->price;
        $item->save();

        return response()->json($item, 201);
    }

    public function update(Request $request, CartItem $item)
    {
        if ($item->cart->user_id !== $request->user()->id) {
            abort(403);
        }

        $data = $request->validate(['quantity' => 'required|integer|min:1']);
        $item->update(['quantity' => $data['quantity']]);

        return response()->json($item);
    }

    public function destroy(CartItem $item)
    {
        if ($item->cart->user_id !== request()->user()->id) {
            abort(403);
        }

        $item->delete();

        return response()->json([], 204);
    }
}
