@extends('layouts.admin')
@section('title', 'Coupons')
@section('page-title', 'Coupon Management')

@section('content')
<div class="mb-4 flex items-center justify-between">
    <p class="text-sm text-slate-500 dark:text-slate-400">
        Platform-wide and hotel-specific discount codes.
    </p>
    <a href="{{ route('admin.coupons.create') }}" class="btn-primary btn-sm">+ New Coupon</a>
</div>

@if(session('success'))
<div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4 text-sm text-emerald-700 dark:text-emerald-300">
    {{ session('success') }}
</div>
@endif

<div class="card">
    @if($coupons->isEmpty())
        <div class="p-8 text-center text-slate-400 dark:text-slate-500">
            <svg class="mx-auto h-10 w-10 mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/>
            </svg>
            <p class="font-medium">No coupons yet</p>
            <p class="text-xs mt-1">Create your first coupon to offer discounts to guests.</p>
        </div>
    @else
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Discount</th>
                    <th>Scope</th>
                    <th>Uses</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($coupons as $coupon)
                <tr class="tr-hover">
                    <td>
                        <span class="font-mono font-bold text-slate-900 dark:text-white tracking-wide">
                            {{ $coupon->code }}
                        </span>
                    </td>
                    <td>
                        @if($coupon->type === 'percentage')
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">{{ $coupon->value }}% off</span>
                        @else
                            <span class="text-emerald-600 dark:text-emerald-400 font-semibold">TZS {{ number_format($coupon->value, 0) }} off</span>
                        @endif
                        @if($coupon->min_booking_amount)
                            <p class="text-xs text-slate-400">min TZS {{ number_format($coupon->min_booking_amount, 0) }}</p>
                        @endif
                    </td>
                    <td>
                        @if($coupon->hotel)
                            <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">
                                {{ $coupon->hotel->name }}
                            </span>
                        @else
                            <span class="text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-full">
                                Platform-wide
                            </span>
                        @endif
                    </td>
                    <td class="text-sm">
                        {{ $coupon->uses }}
                        @if($coupon->max_uses)
                            / {{ $coupon->max_uses }}
                        @else
                            / ∞
                        @endif
                    </td>
                    <td class="text-sm">
                        @if($coupon->expires_at)
                            <span class="{{ $coupon->expires_at->isPast() ? 'text-rose-500' : 'text-slate-600 dark:text-slate-300' }}">
                                {{ $coupon->expires_at->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-slate-400">Never</span>
                        @endif
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}">
                            @csrf
                            <button type="submit"
                                    class="text-xs font-semibold px-2 py-1 rounded-lg transition
                                           {{ $coupon->active
                                               ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400'
                                               : 'bg-slate-100 text-slate-500 hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-400' }}">
                                {{ $coupon->active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}"
                              x-data
                              @submit.prevent="if(confirm('Delete coupon {{ $coupon->code }}?')) $el.submit()">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="text-xs text-rose-500 hover:text-rose-700 font-medium">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
        <div class="p-4">{{ $coupons->links() }}</div>
    @endif
    @endif
</div>
@endsection
