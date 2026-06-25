@extends('layouts.app')

@section('content')
    <section class="space-y-6">
        <div class="rounded-3xl bg-white p-8 shadow-sm">
            <div class="grid gap-6 lg:grid-cols-[1.4fr_0.8fr] lg:items-center">
                <div>
                    <p class="text-sm uppercase tracking-[0.25em] text-amber-600">Premium furniture</p>
                    <h1 class="mt-4 text-4xl font-semibold tracking-tight text-slate-900">Modern furniture for every space.</h1>
                    <p class="mt-4 max-w-2xl text-slate-600">Handcrafted collections designed for comfort, durability, and effortless style. FurniCraft creates furniture that elevates your home.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('shop.index') }}" class="rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Shop now</a>
                        <a href="{{ route('blog.index') }}" class="rounded-full border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Read our blog</a>
                    </div>
                </div>
                <div class="rounded-[2rem] bg-slate-100 p-8 text-center">
                    <p class="text-sm uppercase tracking-[0.2em] text-slate-500">Featured collection</p>
                    <div class="mt-8 grid gap-4">
                        @foreach ($featured as $product)
                            <a href="{{ route('shop.show', $product) }}" class="rounded-3xl bg-white p-5 text-left shadow-sm transition hover:-translate-y-1">
                                <p class="text-sm font-semibold text-slate-900">{{ $product->name }}</p>
                                <p class="mt-2 text-sm text-slate-500">{{ Str::limit($product->description, 70) }}</p>
                                <p class="mt-4 text-lg font-semibold text-slate-900">${{ number_format($product->price / 100, 2) }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            @foreach ($categories as $category)
                <a href="{{ route('shop.category', $category) }}" class="rounded-[2rem] bg-white p-8 text-center shadow-sm transition hover:-translate-y-1">
                    <p class="text-sm uppercase tracking-[0.2em] text-amber-600">{{ $category->name }}</p>
                    <p class="mt-4 text-3xl font-semibold text-slate-900">Shop now</p>
                </a>
            @endforeach
        </div>
    </section>
@endsection
