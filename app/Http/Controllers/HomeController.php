<?php

namespace App\Http\Controllers;

use App\Models\Hotel;

class HomeController extends Controller
{
    public function index()
    {
        if ($hotel = Hotel::currentForGuest()) {
            return redirect()->route('hotels.show', $hotel);
        }

        return view('home');
    }
}
