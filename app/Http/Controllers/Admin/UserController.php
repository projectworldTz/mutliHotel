<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            }))
            ->when($request->role, fn ($q, $r) => $q->whereHas('roles', fn ($q) => $q->where('slug', $r)))
            ->with('roles')
            ->latest()
            ->paginate(20);

        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function show(User $user)
    {
        $user->loadMissing(['roles', 'bookings.hotel', 'ownedHotels']);

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
}
