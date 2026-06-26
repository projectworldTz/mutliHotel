<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelStaff;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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
            'position' => 'required|in:receptionist,manager',
        ]);

        // Find or create the user — track whether this is a brand-new account
        $isNewUser = ! User::where('email', $data['email'])->exists();

        $user = User::firstOrCreate(
            ['email' => $data['email']],
            [
                'name'     => $data['name'] ?? $data['email'],
                'password' => bcrypt(Str::random(32)),
            ]
        );

        // Assign receptionist role
        $role = Role::where('name', 'receptionist')->first();
        if ($role && ! $user->hasRole('receptionist')) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        // Create or reactivate hotel_staff record
        HotelStaff::updateOrCreate(
            ['hotel_id' => $hotel->id, 'user_id' => $user->id],
            ['position' => $data['position'], 'active' => true]
        );

        // Send password setup email for new accounts so they can log in
        if ($isNewUser) {
            Password::sendResetLink(['email' => $user->email]);
            $message = "{$user->name} added as {$data['position']}. A password setup email has been sent to {$user->email}.";
        } else {
            $message = "{$user->name} added as {$data['position']}.";
        }

        return redirect()
            ->route('owner.hotels.staff.index', $hotel)
            ->with('success', $message);
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
