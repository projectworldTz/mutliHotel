<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function redirect()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return match (true) {
            $user->isSuperAdmin()                                  => redirect()->route('admin.dashboard'),
            $user->isHotelOwner()                                  => redirect()->route('owner.dashboard'),
            $user->hasAnyRole(['receptionist', 'manager', 'cashier']) => redirect()->route('receptionist.dashboard'),
            $user->isAccountant()                                  => redirect()->route('accountant.dashboard'),
            default                                                => redirect()->route('account.bookings'),
        };
    }
}
