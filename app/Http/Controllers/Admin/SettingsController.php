<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name'              => 'required|string|max:100',
            'site_email'             => 'required|email|max:255',
            'currency'               => 'required|string|size:3',
            'tax_rate'               => 'required|numeric|min:0|max:100',
            'platform_commission'    => 'required|numeric|min:0|max:100',
            'booking_expiry_minutes' => 'required|integer|min:5',
            'cancellation_policy'    => 'nullable|string',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Settings saved.');
    }
}
