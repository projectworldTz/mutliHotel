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
                    <input type="email" name="settings[support_email]"
                           value="{{ $settings['support_email'] ?? '' }}"
                           class="form-input">
                </div>
                <div>
                    <label class="form-label">Default Currency</label>
                    <select name="settings[currency]" class="form-select">
                        @foreach(['USD','EUR','GBP','JPY','AED'] as $c)
                        <option value="{{ $c }}" {{ ($settings['currency'] ?? 'USD') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
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
                </div>
                <div>
                    <label class="form-label">Max. Advance Booking (days)</label>
                    <input type="number" name="settings[max_advance_days]"
                           value="{{ $settings['max_advance_days'] ?? 365 }}"
                           min="1" class="form-input">
                </div>
                <div>
                    <label class="form-label">Platform Commission (%)</label>
                    <input type="number" name="settings[commission_rate]"
                           value="{{ $settings['commission_rate'] ?? 10 }}"
                           min="0" max="100" step="0.1" class="form-input">
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <button type="submit" class="btn-primary">Save Settings</button>
    </div>
</form>
@endsection
