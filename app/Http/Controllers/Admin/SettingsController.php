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
        $validated = $request->validate([
            'settings.site_name'         => ['required', 'string', 'max:100'],
            'settings.site_email'        => ['required', 'email', 'max:255'],
            'settings.site_phone'        => ['nullable', 'string', 'max:30'],
            'settings.currency'          => ['required', 'string', 'size:3'],
            'settings.min_advance_days'  => ['required', 'integer', 'min:0'],
            'settings.max_advance_days'  => ['required', 'integer', 'min:1'],
            'settings.commission_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Settings saved.');
    }
}
