@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-semibold text-slate-900">Your cart</h1>

        @if ($items->isEmpty())
            <div class="rounded-[2rem] bg-white p-8 shadow-sm text-slate-600">Your cart is empty.</div>
        @else
            <div class="space-y-4">
                @foreach ($items as $item)
                    <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">{{ $item->product->name }}</h2>
                                <p class="mt-2 text-sm text-slate-500">Quantity: {{ $item->quantity }}</p>
                                <p class="mt-1 text-sm text-slate-500">Price: ${{ number_format($item->price / 100, 2) }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="w-20 rounded-full border border-slate-200 px-3 py-2 text-sm" />
                                    <button type="submit" class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Update</button>
                                </form>
                                <form method="POST" action="{{ route('cart.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-slate-200 px-5 py-2 text-sm text-slate-700 transition hover:bg-slate-50">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="rounded-[2rem] bg-white p-8 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-lg font-semibold text-slate-900">Total</p>
                        <p class="text-xl font-semibold text-slate-900">${{ number_format($items->sum(fn ($item) => $item->price * $item->quantity) / 100, 2) }}</p>
                    </div>
                    <div class="mt-6 text-right">
                        <a href="{{ route('checkout.index') }}" class="inline-flex rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Proceed to checkout</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
