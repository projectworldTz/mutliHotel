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
                    <label class="form-label">Default Commission Rate (%)</label>
                    <input type="number" name="settings[default_commission_rate]"
                           value="{{ $settings['default_commission_rate'] ?? 10 }}"
                           min="0" max="100" step="0.1" class="form-input">
                    <p class="mt-1 text-xs text-slate-400">Applied only to newly created hotels going forward. Existing hotels keep whatever rate they were created with.</p>
                </div>
            </div>
        </div>

        {{-- Demo Credentials --}}
        <div class="card p-6 lg:col-span-2">
            <h2 class="text-base font-bold text-slate-900 dark:text-white mb-1">Demo Credentials (Public Page)</h2>
            <p class="text-xs text-slate-400 mb-5">Shown on the public landing page so prospects can log in and explore the platform themselves, without contacting you directly.</p>

            <div>
                <label class="flex items-center gap-2 cursor-pointer mb-5">
                    <input type="hidden" name="settings[demo_credentials_enabled]" value="0">
                    <input type="checkbox" name="settings[demo_credentials_enabled]" value="1"
                        {{ ($settings['demo_credentials_enabled'] ?? '0') == '1' ? 'checked' : '' }}
                        class="rounded accent-navy">
                    <span class="text-sm text-slate-700 dark:text-slate-200">Show demo credentials on the public landing page</span>
                </label>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Hotel Owner Demo</h3>
                    <div>
                        <label class="form-label">Owner Email</label>
                        <input type="email" name="settings[demo_owner_email]"
                               value="{{ $settings['demo_owner_email'] ?? '' }}"
                               placeholder="owner@example.com"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Owner Password</label>
                        <input type="text" name="settings[demo_owner_password]"
                               value="{{ $settings['demo_owner_password'] ?? '' }}"
                               class="form-input">
                    </div>
                </div>
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Super Admin Demo</h3>
                    <div>
                        <label class="form-label">Super Admin Email</label>
                        <input type="email" name="settings[demo_superadmin_email]"
                               value="{{ $settings['demo_superadmin_email'] ?? '' }}"
                               placeholder="admin@example.com"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Super Admin Password</label>
                        <input type="text" name="settings[demo_superadmin_password]"
                               value="{{ $settings['demo_superadmin_password'] ?? '' }}"
                               class="form-input">
                    </div>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-400">Use accounts you're comfortable exposing publicly — anyone can log in with these. Leave a field blank to hide that block on the landing page.</p>
        </div>
    </div>

    <div class="mt-5">
        <button type="submit" class="btn-primary">Save Settings</button>
    </div>
</form>
@endsection
