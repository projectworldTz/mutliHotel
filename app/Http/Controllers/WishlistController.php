<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Auth::user()->wishlist()->with('product')->get();

        return view('wishlist.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        Auth::user()->wishlist()->firstOrCreate(['product_id' => $request->product_id]);

        return redirect()->route('wishlist.index')->with('success', 'Product added to wishlist.');
    }

    public function destroy(Wishlist $item)
    {
        if ($item->user_id !== Auth::id()) {
            abort(403);
        }

        $item->delete();

        return back();
    }
}
