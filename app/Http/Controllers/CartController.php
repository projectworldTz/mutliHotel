<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $items = Auth::user()->cartItems()->with('product')->get();

        return view('cart.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id', 'quantity' => 'integer|min:1']);

        $user = Auth::user();
        $cart = $user->cart()->firstOrCreate([]);

        $item = $cart->items()->firstOrNew(['product_id' => $request->product_id]);
        $item->quantity = $item->exists ? $item->quantity + $request->input('quantity', 1) : $request->input('quantity', 1);
        $item->price = $item->price ?: $item->product->price;
        $item->save();

        return redirect()->route('cart.index')->with('success', 'Product added to cart.');
    }

    public function update(Request $request, CartItem $item)
    {
        if ($item->cart->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate(['quantity' => 'required|integer|min:1']);
        $item->update(['quantity' => $request->quantity]);

        return back();
    }

    public function destroy(CartItem $item)
    {
        if ($item->cart->user_id !== Auth::id()) {
            abort(403);
        }

        $item->delete();

        return back();
    }
}
