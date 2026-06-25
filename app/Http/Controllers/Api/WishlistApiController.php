<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistApiController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->wishlist()->with('product')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate(['product_id' => 'required|exists:products,id']);

        $item = $request->user()->wishlist()->firstOrCreate(['product_id' => $data['product_id']]);

        return response()->json($item, 201);
    }

    public function destroy(Wishlist $item)
    {
        if ($item->user_id !== request()->user()->id) {
            abort(403);
        }

        $item->delete();

        return response()->json([], 204);
    }
}
