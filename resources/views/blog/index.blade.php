@extends('layouts.app')

@section('content')
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-semibold text-slate-900">Furniture stories</h1>
            <p class="mt-2 text-slate-600">Inspiration and design tips from the FurniCraft team.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            @foreach ($posts as $post)
                <article class="rounded-[2rem] bg-white p-6 shadow-sm">
                    <a href="{{ route('blog.show', $post) }}" class="block">
                        <h2 class="text-xl font-semibold text-slate-900">{{ $post->title }}</h2>
                        <p class="mt-3 text-sm text-slate-500">{{ Str::limit($post->excerpt ?? $post->content, 120) }}</p>
                        <p class="mt-4 text-sm text-slate-500">{{ $post->published_at?->format('M j, Y') }}</p>
                    </a>
                </article>
            @endforeach
        </div>

        <div>
            {{ $posts->withQueryString()->links() }}
        </div>
    </div>
@endsection
