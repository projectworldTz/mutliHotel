<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Hotel;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $favorites = auth()->user()
            ->favorites()
            ->with(['hotel.featuredImage', 'hotel.amenities'])
            ->latest()
            ->paginate(12);

        return view('account.favorites', compact('favorites'));
    }

    /**
     * Toggle a hotel favorite (add if absent, remove if present).
     * Accepts JSON (React) or form POST.
     */
    public function toggle(Hotel $hotel, Request $request)
    {
        $user      = auth()->user();
        $existing  = Favorite::where('user_id', $user->id)->where('hotel_id', $hotel->id)->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
            $message   = 'Removed from favourites.';
        } else {
            Favorite::create(['user_id' => $user->id, 'hotel_id' => $hotel->id]);
            $favorited = true;
            $message   = 'Added to favourites!';
        }

        if ($request->expectsJson()) {
            return response()->json(['favorited' => $favorited, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    public function destroy(Hotel $hotel)
    {
        Favorite::where('user_id', auth()->id())
            ->where('hotel_id', $hotel->id)
            ->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Removed from favourites.');
    }
}
