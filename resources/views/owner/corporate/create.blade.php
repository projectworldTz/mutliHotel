@extends('layouts.owner')
@section('title', 'New Corporate Account')
@section('page-title', 'New Corporate Account')

@section('content')

<div class="mb-5 flex items-center gap-2">
    <a href="{{ route('owner.hotels.corporate.index', $hotel) }}" class="btn-ghost btn-sm">← Back</a>
</div>

<div class="max-w-2xl">
    <div class="card p-6">
        <form method="POST" action="{{ route('owner.hotels.corporate.store', $hotel) }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="form-label">Company Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                        class="form-input @error('company_name') border-rose-400 @enderror" required>
                    @error('company_name')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="form-input">
                </div>
            </div>

            <hr class="border-slate-100 dark:border-slate-700">

            {{-- Discount --}}
            <div x-data="{ dtype: '{{ old('discount_type', 'percentage') }}' }">
                <label class="form-label">Negotiated Discount <span class="text-rose-500">*</span></label>
                <div class="flex gap-3 mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="discount_type" value="percentage"
                            x-model="dtype" class="accent-navy"> Percentage (%)
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="discount_type" value="fixed"
                            x-model="dtype" class="accent-navy"> Fixed amount ({{ config('app.currency') }} off/night)
                    </label>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <input type="number" name="discount_value"
                        value="{{ old('discount_value', 10) }}"
                        min="0" step="0.01"
                        class="form-input w-full sm:w-36 @error('discount_value') border-rose-400 @enderror"
                        required>
                    <span class="text-sm text-slate-500" x-text="dtype === 'percentage' ? '% off base rate' : '{{ config('app.currency') }} off per night'"></span>
                </div>
                @error('discount_value')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Credit Limit ({{ config('app.currency') }}) <span class="text-slate-400 text-xs font-normal">optional — max total spend allowed</span></label>
                <input type="number" name="credit_limit" value="{{ old('credit_limit') }}"
                    min="0" step="1000" class="form-input w-full sm:w-48">
            </div>

            <hr class="border-slate-100 dark:border-slate-700">

            {{-- Contract period --}}
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="form-label">Contract Start</label>
                    <input type="date" name="contract_start" value="{{ old('contract_start') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Contract End</label>
                    <input type="date" name="contract_end" value="{{ old('contract_end') }}" class="form-input">
                </div>
            </div>

            <div>
                <label class="form-label">Billing Terms</label>
                <textarea name="billing_terms" rows="2"
                    class="form-input resize-none"
                    placeholder="e.g. Monthly invoice, net 30 days…">{{ old('billing_terms') }}</textarea>
            </div>

            <div>
                <label class="form-label">Internal Notes</label>
                <textarea name="notes" rows="2"
                    class="form-input resize-none"
                    placeholder="Private notes visible only to you…">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('owner.hotels.corporate.index', $hotel) }}" class="btn-ghost btn-sm">Cancel</a>
                <button type="submit" class="btn-primary btn-sm">Create Account & Generate Portal Link</button>
            </div>
        </form>
    </div>
</div>

@endsection
