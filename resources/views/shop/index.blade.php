@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">Shop all furniture</h1>
                <p class="mt-2 text-sm text-slate-600">Browse curated categories and latest designs.</p>
            </div>
            <form method="GET" action="{{ route('shop.index') }}" class="flex flex-wrap gap-3">
                <input type="search" name="search" placeholder="Search products" value="{{ request('search') }}" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-slate-300 focus:outline-none" />
                <button type="submit" class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">Search</button>
            </form>
        </div>

        <div class="grid gap-6 lg:grid-cols-4">
            <aside class="space-y-6 rounded-[2rem] bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-[0.25em] text-slate-500">Categories</h2>
                <div class="space-y-3">
                    @foreach ($categories as $category)
                        <a href="{{ route('shop.category', $category) }}" class="block rounded-2xl px-4 py-3 text-sm text-slate-700 transition hover:bg-slate-50">{{ $category->name }}</a>
                    @endforeach
                </div>
            </aside>

            <section class="lg:col-span-3">
                <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($products as $product)
                        <article class="rounded-[2rem] bg-white p-6 shadow-sm transition hover:-translate-y-1">
                            <a href="{{ route('shop.show', $product) }}" class="block text-left">
                                <div class="h-48 rounded-3xl bg-slate-100"></div>
                                <h3 class="mt-5 text-xl font-semibold text-slate-900">{{ $product->name }}</h3>
                                <p class="mt-2 text-sm text-slate-500">{{ Str::limit($product->description, 90) }}</p>
                                <p class="mt-4 text-lg font-semibold text-slate-900">${{ number_format($product->price / 100, 2) }}</p>
                            </a>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->withQueryString()->links() }}
                </div>
            </section>
        </div>
    </div>
@endsection
