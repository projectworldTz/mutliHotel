@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <h1 class="text-3xl font-semibold text-slate-900">Checkout</h1>

        @if ($items->isEmpty())
            <div class="rounded-[2rem] bg-white p-8 shadow-sm text-slate-600">Your cart is empty. Add items before checking out.</div>
        @else
            <div class="grid gap-8 lg:grid-cols-[1.5fr_1fr]">
                <div class="rounded-[2rem] bg-white p-8 shadow-sm">
                    <h2 class="text-xl font-semibold text-slate-900">Shipping details</h2>
                    <form method="POST" action="{{ route('checkout.store') }}" class="mt-6 space-y-6">
                        @csrf
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="text-sm text-slate-700">Full name</span>
                                <input name="contact_name" type="text" value="{{ old('contact_name') }}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </label>
                            <label class="block">
                                <span class="text-sm text-slate-700">Phone</span>
                                <input name="phone" type="text" value="{{ old('phone') }}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </label>
                        </div>
                        <label class="block">
                            <span class="text-sm text-slate-700">Address line 1</span>
                            <input name="address_line1" type="text" value="{{ old('address_line1') }}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-700">Address line 2</span>
                            <input name="address_line2" type="text" value="{{ old('address_line2') }}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                        </label>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <label class="block">
                                <span class="text-sm text-slate-700">City</span>
                                <input name="city" type="text" value="{{ old('city') }}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </label>
                            <label class="block">
                                <span class="text-sm text-slate-700">State</span>
                                <input name="state" type="text" value="{{ old('state') }}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </label>
                            <label class="block">
                                <span class="text-sm text-slate-700">Postal code</span>
                                <input name="postal_code" type="text" value="{{ old('postal_code') }}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                            </label>
                        </div>
                        <label class="block">
                            <span class="text-sm text-slate-700">Country</span>
                            <input name="country" type="text" value="{{ old('country') }}" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm" />
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-700">Payment method</span>
                            <select name="payment_method" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                                <option value="credit_card">Credit card</option>
                                <option value="paypal">PayPal</option>
                                <option value="bank_transfer">Bank transfer</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-sm text-slate-700">Order notes</span>
                            <textarea name="notes" rows="4" class="mt-2 w-full rounded-3xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm"></textarea>
                        </label>
                        <button type="submit" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Place order</button>
                    </form>
                </div>

                <aside class="rounded-[2rem] bg-white p-8 shadow-sm">
                    <h2 class="text-xl font-semibold text-slate-900">Order summary</h2>
                    <div class="mt-6 space-y-4">
                        @foreach ($items as $item)
                            <div class="rounded-3xl bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-sm font-medium text-slate-900">{{ $item->product->name }}</p>
                                    <p class="text-sm text-slate-600">{{ $item->quantity }} × ${{ number_format($item->price / 100, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 border-t border-slate-200 pt-6">
                        <div class="flex items-center justify-between text-sm text-slate-700">
                            <span>Subtotal</span>
                            <span>${{ number_format($items->sum(fn ($item) => $item->price * $item->quantity) / 100, 2) }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm text-slate-700">
                            <span>Shipping</span>
                            <span>$0.00</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm text-slate-700">
                            <span>Tax</span>
                            <span>$0.00</span>
                        </div>
                        <div class="mt-5 flex items-center justify-between text-lg font-semibold text-slate-900">
                            <span>Total</span>
                            <span>${{ number_format($items->sum(fn ($item) => $item->price * $item->quantity) / 100, 2) }}</span>
                        </div>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
