<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'FurniCraft') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white border-b border-slate-200">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="text-xl font-semibold text-slate-900">FurniCraft</a>
                <nav class="flex items-center gap-4 text-sm text-slate-700">
                    <a href="{{ route('shop.index') }}" class="hover:text-slate-900">Shop</a>
                    <a href="{{ route('blog.index') }}" class="hover:text-slate-900">Blog</a>
                    <a href="{{ route('cart.index') }}" class="hover:text-slate-900">Cart</a>
                    <a href="{{ route('wishlist.index') }}" class="hover:text-slate-900">Wishlist</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="hover:text-slate-900">Account</a>
                        @can('access-admin')
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-900">Admin</a>
                        @endcan
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-slate-700 hover:text-slate-900">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-slate-900">Login</a>
                        <a href="{{ route('register') }}" class="hover:text-slate-900">Register</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('warning'))
                    <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        {{ session('warning') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-white py-6">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 text-sm text-slate-500 sm:px-6 lg:px-8">
                <span>© {{ date('Y') }} FurniCraft. Crafted for premium interiors.</span>
                <span><a href="mailto:hello@furnicraft.example" class="hover:text-slate-900">hello@furnicraft.example</a></span>
            </div>
        </footer>
    </div>
</body>
</html>
