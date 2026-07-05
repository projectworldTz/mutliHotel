@extends('layouts.admin')
@section('title', 'Settings')
@section('page-title', 'Platform Settings')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- General --}}
        <div class="card p-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">General</h2>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Site Name</label>
                    <input type="text" name="settings[site_name]"
                           value="{{ $settings['site_name'] ?? config('app.name') }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Support Email</label>
                    <input type="email" name="settings[site_email]"
                           value="{{ $settings['site_email'] ?? '' }}"
                           class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Support Phone</label>
                    <input type="text" name="settings[site_phone]"
                           value="{{ $settings['site_phone'] ?? '' }}"
                           placeholder="+255 700 000 000"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Platform Currency</label>
                    <input type="text" class="form-input bg-slate-100 dark:bg-slate-800" value="{{ config('app.currency') }}" disabled>
                    <p class="mt-1 text-xs text-slate-400">Set via <code>APP_CURRENCY</code> in the server environment, not here — it's used directly by payment gateways and can't be changed per-request.</p>
                </div>
            </div>
        </div>

        {{-- Booking --}}
        <div class="card p-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-5">Booking Rules</h2>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Min. Advance Booking (days)</label>
                    <input type="number" name="settings[min_advance_days]"
                           value="{{ $settings['min_advance_days'] ?? 0 }}"
                           min="0" class="form-input">
                    <p class="mt-1 text-xs text-slate-400">How soon a guest may book before check-in. 0 = same-day booking allowed.</p>
                </div>
                <div>
                    <label class="form-label">Max. Advance Booking (days)</label>
                    <input type="number" name="settings[max_advance_days]"
                           value="{{ $settings['max_advance_days'] ?? 365 }}"
                           min="1" class="form-input">
                    <p class="mt-1 text-xs text-slate-400">How far in the future a check-in date may be selected.</p>
                </div>
                <div>
                    <label class="form-label">Booking Tax Rate (%)</label>
                    <input type="number" name="settings[booking_tax_rate]"
                           value="{{ $settings['booking_tax_rate'] ?? 10 }}"
                           min="0" max="100" step="0.1" class="form-input">
                    <p class="mt-1 text-xs text-slate-400">Applied to every booking's subtotal, whether made by a guest online or a receptionist at the desk.</p>
                </div>
                <div>
                    <label class="form-label">Default Commission Rate (%)</label>
                    <input type="number" name="settings[default_commission_rate]"
                           value="{{ $settings['default_commission_rate'] ?? 10 }}"
                           min="0" max="100" step="0.1" class="form-input">
                    <p class="mt-1 text-xs text-slate-400">Applied only to newly created hotels going forward. Existing hotels keep whatever rate they were created with.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <button type="submit" class="btn-primary">Save Settings</button>
    </div>
</form>
@endsection
