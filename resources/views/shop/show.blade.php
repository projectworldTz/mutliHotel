@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <div class="grid gap-8 lg:grid-cols-[1.4fr_0.8fr]">
            <section class="rounded-[2rem] bg-white p-8 shadow-sm">
                <div class="h-96 rounded-3xl bg-slate-100"></div>
                <h1 class="mt-8 text-3xl font-semibold text-slate-900">{{ $product->name }}</h1>
                <p class="mt-4 text-lg text-slate-500">{{ $product->description }}</p>
                <div class="mt-6 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Price</p>
                        <p class="mt-2 text-3xl font-semibold text-slate-900">${{ number_format($product->price / 100, 2) }}</p>
                    </div>
                    <form method="POST" action="{{ route('cart.store') }}" class="flex items-center gap-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}" />
                        <input type="number" name="quantity" value="1" min="1" class="w-20 rounded-full border border-slate-200 px-4 py-2 text-sm" />
                        <button type="submit" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Add to cart</button>
                    </form>
                </div>
            </section>

            <aside class="space-y-6 rounded-[2rem] bg-white p-8 shadow-sm">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Category</p>
                    <p class="mt-2 text-sm text-slate-900">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                </div>
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Brand</p>
                    <p class="mt-2 text-sm text-slate-900">{{ $product->brand?->name ?? 'Independent' }}</p>
                </div>
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Availability</p>
                    <p class="mt-2 text-sm text-slate-900">{{ $product->stock > 0 ? 'In stock' : 'Out of stock' }}</p>
                </div>
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Related products</p>
                    <div class="mt-4 space-y-3">
                        @foreach ($related as $relatedProduct)
                            <a href="{{ route('shop.show', $relatedProduct) }}" class="block rounded-3xl bg-slate-100 px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-200">{{ $relatedProduct->name }}</a>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
