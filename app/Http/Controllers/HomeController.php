<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        // Only honor real subdomain tenancy (e.g. tranquiloo.example.com) — the
        // marketplace root must not redirect based on the last hotel a guest browsed.
        if (app()->bound('current_hotel')) {
            return redirect()->route('hotels.show', app('current_hotel'));
        }

        return view('home');
    }
}
