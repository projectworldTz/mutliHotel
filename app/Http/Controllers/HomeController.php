<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        // Only honor real subdomain tenancy (e.g. tranquiloo.example.com) — the
        // marketplace root must not redirect based on the last hotel a guest browsed.
        if (app()->bound('current_hotel')) {
            return redirect()->route('hotels.show', app('current_hotel'));
        }

        $demoCredentials = null;

        if (Setting::get('demo_credentials_enabled') == '1') {
            $demoCredentials = [
                'owner_email'         => Setting::get('demo_owner_email'),
                'owner_password'      => Setting::get('demo_owner_password'),
                'superadmin_email'    => Setting::get('demo_superadmin_email'),
                'superadmin_password' => Setting::get('demo_superadmin_password'),
            ];
        }

        return view('home', compact('demoCredentials'));
    }
}
