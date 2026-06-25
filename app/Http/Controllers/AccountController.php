<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        return view('account.dashboard', [
            'orders' => $user->orders()->count(),
            'wishlist' => $user->wishlist()->count(),
            'addresses' => $user->addresses()->count(),
        ]);
    }

    public function orders()
    {
        $orders = Auth::user()->orders()->with('items.product')->latest()->get();

        return view('account.orders', compact('orders'));
    }

    public function addresses()
    {
        $addresses = Auth::user()->addresses()->latest()->get();

        return view('account.addresses', compact('addresses'));
    }

    public function settings()
    {
        return view('account.settings', ['user' => Auth::user()]);
    }
}
