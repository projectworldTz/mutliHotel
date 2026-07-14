<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function dashboard()
    {
        $user  = Auth::user();
        $stats = [
            'total'     => $user->bookings()->count(),
            'upcoming'  => $user->upcomingBookings()->count(),
            'completed' => $user->bookings()->where('status', Booking::STATUS_CHECKED_OUT)->count(),
            'cancelled' => $user->bookings()->where('status', Booking::STATUS_CANCELLED)->count(),
        ];
        $recentBookings = $user->bookings()
            ->with(['hotel.featuredImage'])
            ->latest()
            ->take(5)
            ->get();

        return view('account.dashboard', compact('stats', 'recentBookings'));
    }

    public function bookings(Request $request)
    {
        $status   = $request->input('status');
        $bookings = $this->bookingService->getUserBookings(auth()->user(), 10);

        return view('account.bookings', compact('bookings', 'status'));
    }

    public function showBooking(string $bookingNumber)
    {
        $booking = $this->bookingService->findByNumber($bookingNumber);
        abort_unless($booking->user_id === auth()->id(), 403);

        $booking->loadMissing(['hotel.images', 'rooms.roomType', 'payment', 'invoice']);

        return view('account.booking-detail', compact('booking'));
    }

    public function profile()
    {
        return view('account.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'marketing_opt_in' => 'required|boolean',
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }
}
