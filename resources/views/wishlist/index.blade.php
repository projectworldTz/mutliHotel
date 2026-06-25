@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <h1 class="text-3xl font-semibold text-slate-900">Your wishlist</h1>

        @if ($items->isEmpty())
            <div class="rounded-[2rem] bg-white p-8 shadow-sm text-slate-600">Your wishlist is empty.</div>
        @else
            <div class="space-y-4">
                @foreach ($items as $item)
                    <div class="rounded-[2rem] bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-900">{{ $item->product->name }}</h2>
                                <p class="mt-2 text-sm text-slate-500">${{ number_format($item->product->price / 100, 2) }}</p>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <form method="POST" action="{{ route('cart.store') }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->product->id }}" />
                                    <button type="submit" class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Add to cart</button>
                                </form>
                                <form method="POST" action="{{ route('wishlist.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-full border border-slate-200 px-5 py-2 text-sm text-slate-700 transition hover:bg-slate-50">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
