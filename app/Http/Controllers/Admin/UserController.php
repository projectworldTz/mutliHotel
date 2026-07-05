<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        $ownerRole = Role::where('name', 'hotel-owner')->firstOrFail();
        $user->roles()->attach($ownerRole->id);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "Hotel owner account created for {$user->name}. They can now log in and register their hotel.");
    }

    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->role, fn ($q, $r) => $q->whereHas('roles', fn ($q) => $q->where('name', $r)))
            ->when($request->hotel_id, fn ($q, $h) => $q->where(function ($q) use ($h) {
                $q->whereHas('ownedHotels', fn ($q2) => $q2->where('hotels.id', $h))
                  ->orWhereHas('staffAssignments', fn ($q2) => $q2->where('hotel_id', $h));
            }))
            ->with(['roles', 'ownedHotels:id,name,slug,owner_id', 'staffAssignments' => fn ($q) => $q->where('active', true)->with('hotel:id,name,slug')])
            ->latest()
            ->paginate(20);

        $roles  = Role::all();
        $hotels = Hotel::orderBy('name')->get(['id', 'name']);

        return view('admin.users.index', compact('users', 'roles', 'hotels'));
    }

    public function show(User $user)
    {
        $user->loadMissing(['roles', 'bookings.hotel', 'ownedHotels', 'staffAssignments.hotel']);

        return view('admin.users.show', compact('user'));
    }

    public function assignRole(User $user, Request $request)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);

        $role = Role::findOrFail($request->role_id);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return back()->with('success', "Role \"{$role->name}\" assigned to {$user->name}.");
    }

    public function revokeRole(User $user, Role $role)
    {
        $user->roles()->detach($role->id);

        return back()->with('success', "Role \"{$role->name}\" revoked from {$user->name}.");
    }

    public function toggleActive(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot deactivate yourself.');

        $user->update(['is_active' => ! ($user->is_active ?? true)]);

        $state = $user->fresh()->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "{$user->name} has been {$state}.");
    }

    public function updateHotelLimit(User $user, Request $request)
    {
        $data = $request->validate([
            'max_hotels' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $user->update(['max_hotels' => $data['max_hotels']]);

        return back()->with('success', "{$user->name} can now register up to {$data['max_hotels']} " . Str::plural('hotel', $data['max_hotels']) . '.');
    }
}
