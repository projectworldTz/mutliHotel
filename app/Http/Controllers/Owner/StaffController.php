<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelStaff;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    public function index(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        $staff = $hotel->staff()->with('user')->latest()->get();

        return view('owner.staff.index', compact('hotel', 'staff'));
    }

    public function create(Hotel $hotel)
    {
        $this->authorizeHotel($hotel);

        return view('owner.staff.create', compact('hotel'));
    }

    public function store(Hotel $hotel, Request $request)
    {
        $this->authorizeHotel($hotel);

        $data = $request->validate([
            'email'    => 'required|email|max:255',
            'name'     => 'required_without:existing_user|string|max:255',
            'position' => 'required|in:receptionist,manager,cashier,accountant',
        ]);

        // Find or create the user — track whether this is a brand-new account
        $isNewUser    = ! User::where('email', $data['email'])->exists();
        $tempPassword = $isNewUser ? Str::password(10, symbols: false) : null;

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'] ?? $data['email'],
                'password' => bcrypt($tempPassword ?? Str::random(32)),
            ]
        );

        // Assign the matching staff role dynamically
        $role = Role::where('name', $data['position'])->first();
        if ($role && ! $user->hasRole($data['position'])) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        // Create or reactivate hotel_staff record
        HotelStaff::updateOrCreate(
            ['hotel_id' => $hotel->id, 'user_id' => $user->id],
            ['position' => $data['position'], 'active' => true]
        );

        $redirect = redirect()
            ->route('owner.hotels.staff.index', $hotel)
            ->with('success', "{$user->name} added as {$data['position']}.");

        // Hand the owner a one-time temporary password to give the new staff
        // member directly — they can change it later from Account Settings.
        if ($isNewUser) {
            $redirect->with([
                'temp_password_email' => $user->email,
                'temp_password'       => $tempPassword,
            ]);
        }

        return $redirect;
    }

    public function toggleActive(Hotel $hotel, User $user)
    {
        $this->authorizeHotel($hotel);

        $staff = HotelStaff::where('hotel_id', $hotel->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $staff->update(['active' => ! $staff->active]);

        $state = $staff->active ? 'activated' : 'deactivated';

        return back()->with('success', "{$user->name} {$state}.");
    }

    public function destroy(Hotel $hotel, User $user)
    {
        $this->authorizeHotel($hotel);

        HotelStaff::where('hotel_id', $hotel->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', "{$user->name} removed from hotel staff.");
    }

    private function authorizeHotel(Hotel $hotel): void
    {
        abort_unless(
            auth()->user()->isSuperAdmin() ||
            $hotel->owner_id === auth()->id(),
            403
        );
    }
}
