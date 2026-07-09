@extends('layouts.app')
@section('title', __('Checkout'))

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="page-header">
        <h1 class="page-title">{{ __('Checkout') }}</h1>
    </div>

    @if($errors->any())
    <div class="mb-5 rounded-xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 px-4 py-3 text-sm text-rose-700 dark:text-rose-300">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('booking.store') }}"
          x-data="checkoutForm({{ $manualPayment ? 'true' : 'false' }})"
          @submit.prevent="submit($event)">
        {{-- Alpine.js component handles submit loading state --}}
        @csrf
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-5">

                {{-- Guest Details --}}
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-4">{{ __('Guest Details') }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">{{ __('Adults') }}</label>
                            <input type="number" name="guests_adults" value="{{ old('guests_adults', 1) }}"
                                   min="1" max="20" class="form-input @error('guests_adults') border-rose-500 @enderror">
                            @error('guests_adults') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">{{ __('Children') }}</label>
                            <input type="number" name="guests_children" value="{{ old('guests_children', 0) }}"
                                   min="0" max="10" class="form-input">
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="form-label">
                            {{ __('Special Requests') }}
                            <span class="font-normal text-slate-400">({{ __('optional') }})</span>
                        </label>
                        <textarea name="special_requests" rows="3" class="form-textarea"
                                  placeholder="{{ __('e.g. early check-in, ground floor, dietary requirements…') }}">{{ old('special_requests') }}</textarea>
                    </div>
                </div>

                {{-- Payment Method --}}
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">{{ __('Payment Method') }}</h2>

                    @php
                        $methodMeta = [
                            'airtel_money' => ['label' => 'Airtel Money', 'color' => 'bg-red-500',     'text' => 'text-red-600 dark:text-red-400',           'abbr' => 'AM', 'type' => 'Mobile Money'],
                            'mpesa'        => ['label' => 'M-Pesa',       'color' => 'bg-emerald-600', 'text' => 'text-emerald-600 dark:text-emerald-400',    'abbr' => 'MP', 'type' => 'Mobile Money'],
                            'halotel'      => ['label' => 'Halotel',      'color' => 'bg-orange-500',  'text' => 'text-orange-600 dark:text-orange-400',      'abbr' => 'HL', 'type' => 'Mobile Money'],
                            'mix_by_yas'   => ['label' => 'Mix by Yas',   'color' => 'bg-blue-600',    'text' => 'text-blue-600 dark:text-blue-400',          'abbr' => 'MX', 'type' => 'Mobile Money'],
                            'dpo_card'     => ['label' => 'Card Payment', 'color' => 'bg-purple-600',  'text' => 'text-purple-600 dark:text-purple-400',      'abbr' => 'CARD', 'type' => 'Visa, Mastercard'],
                        ];
                    @endphp

                    @if($manualPayment)
                    <div class="rounded-xl bg-sky-50 dark:bg-sky-900/20 border border-sky-200 dark:border-sky-700 p-4 text-sm text-sky-800 dark:text-sky-200">
                        {{ __('Online payment is currently unavailable for this hotel. Please make your payment using one of the numbers below, then contact the hotel to confirm your booking.') }}
                    </div>

                    @if(!empty($manualNumbers))
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($manualNumbers as $key => $entry)
                        @php $meta = $methodMeta[$key] ?? ['label' => ucfirst(str_replace('_', ' ', $key)), 'color' => 'bg-slate-500', 'abbr' => '?']; @endphp
                        <div class="flex items-center gap-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 p-3.5">
                            <span class="h-10 w-10 rounded-full {{ $meta['color'] }} flex items-center justify-center text-white font-bold text-sm shrink-0">
                                {{ $meta['abbr'] }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $meta['label'] }}</p>
                                <p class="text-sm font-mono text-slate-600 dark:text-slate-300">{{ $entry['number'] }}</p>
                                @if(!empty($entry['name']))
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Name') }}: {{ $entry['name'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($hotel?->phone || $hotel?->email)
                    <p class="mt-4 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('Contact the hotel to confirm your booking:') }}
                        @if($hotel->phone) <strong class="text-slate-700 dark:text-slate-300">{{ $hotel->phone }}</strong> @endif
                        @if($hotel->email) &middot; <strong class="text-slate-700 dark:text-slate-300">{{ $hotel->email }}</strong> @endif
                    </p>
                    @endif
                    @else
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('Select your mobile money provider') }}</p>
                    @error('payment_method') <p class="form-error mb-3">{{ $message }}</p> @enderror

                    @if(empty($gateways))
                    <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-4 text-sm text-amber-800 dark:text-amber-200">
                        {{ __('No payment methods are currently available for this hotel. Please contact the hotel.') }}
                    </div>
                    @else
                    {{-- Hidden input carries the selected value on submit --}}
                    <input type="hidden" name="payment_method" :value="payment">

                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($gateways as $gateway)
                        @php $meta = $methodMeta[$gateway['key']] ?? ['color' => 'bg-slate-500', 'text' => 'text-slate-500', 'abbr' => '?']; @endphp
                        <button type="button"
                                @click="payment = '{{ $gateway['key'] }}'"
                                class="flex w-full items-center gap-3 rounded-xl border-2 p-4 transition-all text-left"
                                :class="payment === '{{ $gateway['key'] }}'
                                    ? 'border-navy bg-navy/5 dark:border-navy-light dark:bg-navy/10 shadow-sm'
                                    : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600'">
                            @if(file_exists(public_path('images/payments/' . $gateway['key'] . '.png')))
                            <img src="{{ asset('images/payments/' . $gateway['key'] . '.png') }}"
                                 alt="{{ $gateway['label'] }}"
                                 class="h-10 w-10 rounded-full object-contain shrink-0 shadow-sm">
                            @else
                            <span class="h-10 w-10 rounded-full {{ $meta['color'] }} flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-sm">
                                {{ $meta['abbr'] }}
                            </span>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $gateway['label'] }}</p>
                                <p class="text-xs {{ $meta['text'] }}">{{ $meta['type'] ?? 'Mobile Money' }}</p>
                            </div>
                            <div :class="payment === '{{ $gateway['key'] }}' ? 'opacity-100' : 'opacity-0'" class="transition-opacity shrink-0">
                                <svg class="h-5 w-5 text-navy dark:text-navy-light" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </button>
                        @endforeach
                    </div>
                    @endif

                    {{-- Card payment panel — redirect notice, no phone number needed --}}
                    <div x-show="payment === 'dpo_card'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-cloak
                         class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-700">
                        <div class="flex items-center gap-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 px-4 py-3">
                            <svg class="h-5 w-5 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <p class="text-sm text-purple-800 dark:text-purple-200">
                                {{ __('You will be redirected to a secure page to pay') }}
                                <strong class="font-bold">{{ money(($cart->sub_total ?? 0) - ($cart->discount ?? 0)) }}</strong>
                                {{ __('by card.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Phone number panel — slides in when a mobile money method is selected --}}
                    <div x-show="payment !== '' && payment !== 'dpo_card'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-cloak
                         class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-700">

                        <label class="form-label">
                            {{ __('Phone Number') }}
                            <span x-text="providerLabel ? '(' + providerLabel + ')' : ''"></span>
                        </label>

                        {{-- Valid prefix hint per provider --}}
                        <p class="mb-2 text-xs text-slate-500 dark:text-slate-400" x-text="prefixHint"></p>

                        <div class="flex rounded-lg overflow-hidden border-2 transition-colors"
                             :class="phoneTouched && phoneError
                                ? 'border-rose-400'
                                : (phone.length === 9 && !phoneError
                                    ? 'border-emerald-400'
                                    : 'border-slate-300 dark:border-slate-600 focus-within:border-navy dark:focus-within:border-navy-light')">
                            <span class="inline-flex items-center px-3 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-sm font-mono border-r-2 border-slate-300 dark:border-slate-600 select-none shrink-0">
                                +255
                            </span>
                            <input type="tel" name="phone_number"
                                   :value="phone"
                                   @input="handlePhoneInput($event)"
                                   @blur="phoneTouched = true"
                                   placeholder="7XX XXX XXX"
                                   maxlength="9"
                                   inputmode="numeric"
                                   class="flex-1 px-3 py-2.5 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none font-mono tracking-widest">
                            <span class="inline-flex items-center pr-3 shrink-0">
                                <svg x-show="phone.length === 9 && !phoneError"
                                     class="h-5 w-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <svg x-show="phoneTouched && phoneError"
                                     class="h-5 w-5 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        </div>

                        <p x-show="phoneTouched && phoneError" x-text="phoneError"
                           class="form-error mt-1" x-cloak></p>
                        @error('phone_number')
                        <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
                        <p class="mt-1.5 text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Enter 9 digits only — no leading 0 or country code.') }}
                        </p>

                        {{-- Amount notice --}}
                        <div class="mt-3 flex items-center gap-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 px-4 py-3">
                            <svg class="h-5 w-5 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-amber-800 dark:text-amber-200">
                                {{ __('You will be charged') }}
                                <strong class="font-bold">{{ money(($cart->sub_total ?? 0) - ($cart->discount ?? 0)) }}</strong>
                                {{ __('via') }} <span x-text="providerLabel"></span>.
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Terms --}}
                <div>
                    <label class="flex cursor-pointer items-start gap-3">
                        <input type="checkbox" name="agree_terms" value="1"
                               class="mt-0.5 rounded border-slate-300 text-navy @error('agree_terms') border-rose-500 @enderror">
                        <span class="text-sm text-slate-600 dark:text-slate-300">
                            {{ __('I agree to the') }}
                            <a href="#" class="text-navy dark:text-navy-light underline">{{ __('Terms of Service') }}</a>
                            {{ __('and') }}
                            <a href="#" class="text-navy dark:text-navy-light underline">{{ __('Cancellation Policy') }}</a>.
                        </span>
                    </label>
                    @error('agree_terms') <p class="form-error mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Order Summary --}}
            <div>
                <div class="card p-5 sticky top-20">
                    <h3 class="font-bold text-slate-900 dark:text-white mb-4">{{ __('Order Summary') }}</h3>

                    @foreach($cart->items as $item)
                    <div class="mb-3 pb-3 border-b border-slate-100 dark:border-slate-700 last:border-0 last:mb-0 last:pb-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $item->roomType->name ?? __('Room') }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->roomType->hotel->name ?? '' }}</p>
                        <div class="mt-1 flex justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span>
                                {{ \Carbon\Carbon::parse($item->check_in)->format('d M') }}
                                –
                                {{ \Carbon\Carbon::parse($item->check_out)->format('d M Y') }}
                            </span>
                            <span class="font-semibold">{{ money($item->sub_total ?? 0) }}</span>
                        </div>
                    </div>
                    @endforeach

                    <div class="mt-3 border-t border-slate-100 dark:border-slate-700 pt-3 space-y-1.5 text-sm">
                        <div class="flex justify-between text-slate-600 dark:text-slate-300">
                            <span>{{ __('Subtotal') }}</span>
                            <span>{{ money($cart->sub_total ?? 0) }}</span>
                        </div>
                        @if(($cart->discount ?? 0) > 0)
                        <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                            <span>{{ __('Discount') }}</span>
                            <span>−{{ money($cart->discount) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between font-bold text-base text-slate-900 dark:text-white pt-1 border-t border-slate-100 dark:border-slate-700">
                            <span>{{ __('Total') }}</span>
                            <span>{{ money(($cart->sub_total ?? 0) - ($cart->discount ?? 0)) }}</span>
                        </div>
                    </div>

                    <button type="submit"
                            class="btn-gold w-full mt-5 transition-all relative"
                            :disabled="!canSubmit"
                            :class="!canSubmit ? 'opacity-60 cursor-not-allowed' : ''">
                        <span x-show="submitting" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            {{ __('Submitting…') }}
                        </span>
                        @if($manualPayment)
                        <span x-show="!submitting">{{ __('Confirm Booking') }}</span>
                        @else
                        <span x-show="!submitting && payment === 'dpo_card'">
                            {{ __('Continue to Secure Card Payment') }}
                        </span>
                        <span x-show="!submitting && payment !== '' && payment !== 'dpo_card' && phone !== ''">
                            {{ __('Pay with') }} <span x-text="providerLabel"></span>
                        </span>
                        <span x-show="!submitting && payment !== 'dpo_card' && (payment === '' || !isPhoneValid)">
                            {{ __('Select payment method & enter number') }}
                        </span>
                        @endif
                    </button>

                    <p class="mt-3 text-center text-xs text-slate-400 dark:text-slate-500" x-show="!manual">
                        <span x-show="payment === 'dpo_card'">{{ __("You'll enter your card details on the next page.") }}</span>
                        <span x-show="payment !== 'dpo_card'">{{ __('You will receive a PIN prompt on your phone after clicking Pay.') }}</span>
                    </p>
                    @if($manualPayment)
                    <p class="mt-3 text-center text-xs text-slate-400 dark:text-slate-500">
                        {{ __('Your booking will be held pending manual payment confirmation by the hotel.') }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
@push('scripts')
<script>
function checkoutForm(manual) {
    return {
        manual:       manual,
        payment:      '{{ old('payment_method', '') }}',
        phone:        '{{ old('phone_number',   '') }}',
        phoneTouched: false,
        submitting:   false,

        names: {
            airtel_money: 'Airtel Money',
            mpesa:        'M-Pesa',
            halotel:      'Halotel',
            mix_by_yas:   'Mix by Yas',
            dpo_card:     'Card Payment',
        },

        // Valid 2-digit prefixes per network
        prefixes: {
            airtel_money: ['68', '69', '78'],
            mpesa:        ['74', '75', '76'],
            halotel:      ['62'],
            mix_by_yas:   ['71'],
        },

        hints: {
            airtel_money: 'Valid prefixes: 68x, 69x, 78x',
            mpesa:        'Valid prefixes: 74x, 75x, 76x',
            halotel:      'Valid prefixes: 62x',
            mix_by_yas:   'Valid prefixes: 71x',
        },

        get providerLabel() {
            return this.names[this.payment] || '';
        },

        get prefixHint() {
            return this.hints[this.payment] || '';
        },

        get isPhoneValid() {
            if (this.phone.length !== 9) return false;
            if (!/^\d{9}$/.test(this.phone)) return false;
            const allowed = this.prefixes[this.payment] || [];
            return allowed.some(p => this.phone.startsWith(p));
        },

        get phoneError() {
            if (this.phone === '') return '';
            if (!/^\d+$/.test(this.phone)) return 'Only digits are allowed — no spaces or dashes.';
            if (this.phone.length < 9) return 'Phone number must be exactly 9 digits (e.g. 712 345 678).';
            const allowed = this.prefixes[this.payment] || [];
            if (allowed.length && !allowed.some(p => this.phone.startsWith(p))) {
                const friendly = {
                    airtel_money: 'Airtel Money (68x, 69x, 78x)',
                    mpesa:        'M-Pesa (74x, 75x, 76x)',
                    halotel:      'Halotel (62x)',
                    mix_by_yas:   'Mix by Yas (71x)',
                };
                return 'This number is not registered on ' + (friendly[this.payment] || this.providerLabel) + '.';
            }
            return '';
        },

        get canSubmit() {
            if (this.manual) return !this.submitting;
            if (this.payment === 'dpo_card') return !this.submitting;
            return this.payment !== '' && this.isPhoneValid && !this.submitting;
        },

        // Strip country code, leading 0, non-digits; cap at 9 digits
        normalizePhone(val) {
            val = val.replace(/\D/g, '');           // digits only
            if (val.startsWith('255')) val = val.slice(3);
            if (val.startsWith('0'))   val = val.slice(1);
            return val.slice(0, 9);
        },

        handlePhoneInput(e) {
            this.phone = this.normalizePhone(e.target.value);
            e.target.value = this.phone;           // keep cursor-friendly
            this.phoneTouched = true;
        },

        submit(e) {
            this.phoneTouched = true;
            if (!this.canSubmit) return;
            this.submitting = true;
            this.$nextTick(() => e.target.submit());
        },
    };
}
</script>
@endpush

@endsection
