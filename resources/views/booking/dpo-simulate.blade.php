<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DPO Pay — Secure Checkout (Sandbox)</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="mb-4 rounded-xl bg-amber-100 border border-amber-300 px-4 py-2.5 text-center text-xs font-semibold text-amber-800">
            ⚙ {{ __('DEVELOPMENT SANDBOX') }} — {{ __('stand-in for the real DPO Pay hosted checkout page') }}
        </div>

        <div class="rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden">
            <div class="bg-slate-900 px-6 py-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="text-white font-bold text-sm">DPO Pay Secure Checkout</span>
            </div>

            <div class="p-6">
                <p class="text-xs text-slate-500 mb-1">{{ __('Amount due') }}</p>
                <p class="text-2xl font-bold text-slate-900 mb-5">{{ money($payment->amount) }}</p>

                <form class="space-y-4" onsubmit="return false">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Card Number') }}</label>
                        <input type="text" placeholder="4111 1111 1111 1111" disabled
                               class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-400">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('Expiry') }}</label>
                            <input type="text" placeholder="MM/YY" disabled
                                   class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">{{ __('CVV') }}</label>
                            <input type="text" placeholder="•••" disabled
                                   class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2.5 text-sm text-slate-400">
                        </div>
                    </div>
                </form>

                <p class="mt-4 text-xs text-slate-400">
                    {{ __('Card fields are disabled in this sandbox — no real card gateway is connected yet. Use the button below to simulate a successful payment and continue the booking flow.') }}
                </p>

                <form method="POST" action="{{ route('dev.payment.confirm', $payment->id) }}" class="mt-5">
                    @csrf
                    <button type="submit"
                            class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-sm py-3 transition">
                        {{ __('Simulate Successful Payment') }} — {{ money($payment->amount) }}
                    </button>
                </form>

                <a href="{{ route('booking.checkout') }}" class="mt-3 block text-center text-xs text-slate-400 hover:text-slate-600">
                    {{ __('Cancel and return to checkout') }}
                </a>
            </div>
        </div>
    </div>

</body>
</html>
