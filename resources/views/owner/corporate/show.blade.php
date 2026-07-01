@extends('layouts.owner')
@section('title', $corporate->company_name)
@section('page-title', $corporate->company_name)

@section('content')

<div class="mb-5 flex flex-wrap items-center gap-2">
    <a href="{{ route('owner.hotels.corporate.index', $hotel) }}" class="btn-ghost btn-sm">← Corporate Accounts</a>
    <a href="{{ route('owner.hotels.corporate.edit', [$hotel, $corporate]) }}" class="btn-outline btn-sm ml-auto">Edit Account</a>

    <form method="POST" action="{{ route('owner.hotels.corporate.regenerate', [$hotel, $corporate]) }}">
        @csrf @method('PATCH')
        <button type="submit"
            onclick="return confirm('Regenerate portal link? The old link will stop working immediately.')"
            class="btn-ghost btn-sm text-amber-600 dark:text-amber-400">
            ↻ Regenerate Link
        </button>
    </form>

    <form method="POST" action="{{ route('owner.hotels.corporate.destroy', [$hotel, $corporate]) }}">
        @csrf @method('DELETE')
        <button type="submit"
            onclick="return confirm('Delete this corporate account?')"
            class="btn-ghost btn-sm text-rose-500">
            Delete
        </button>
    </form>
</div>

@if(session('success'))
<div class="mb-5 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
    {{ session('success') }}
</div>
@endif

<div class="grid gap-6 lg:grid-cols-3">

    {{-- Left: account details --}}
    <div class="space-y-5">

        {{-- Portal link card --}}
        <div class="card p-5">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Corporate Portal Link</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2">Share this link with {{ $corporate->company_name }} employees so they can book at their negotiated rate.</p>
            <div class="rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 p-2 mb-2">
                <code class="text-xs text-navy dark:text-navy-light break-all select-all">
                    {{ route('corporate.portal', [$hotel->slug, $corporate->access_code]) }}
                </code>
            </div>
            <button
                x-data
                @click="
                    navigator.clipboard.writeText('{{ route('corporate.portal', [$hotel->slug, $corporate->access_code]) }}');
                    $el.textContent = 'Copied!';
                    setTimeout(() => $el.textContent = 'Copy Link', 1500)
                "
                class="btn-primary btn-sm w-full">Copy Link</button>
        </div>

        {{-- Account details --}}
        <div class="card p-5">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Account Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd>
                        @if($corporate->is_active && $corporate->isContractActive())
                            <span class="badge badge-confirmed">Active</span>
                        @elseif(!$corporate->is_active)
                            <span class="badge badge-cancelled">Inactive</span>
                        @else
                            <span class="badge badge-pending">Contract Expired</span>
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Discount</dt>
                    <dd class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $corporate->discountLabel() }}</dd>
                </div>
                @if($corporate->credit_limit)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Credit Limit</dt>
                    <dd>{{ money($corporate->credit_limit) }}</dd>
                </div>
                @endif
                @if($corporate->contact_name)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Contact</dt>
                    <dd>{{ $corporate->contact_name }}</dd>
                </div>
                @endif
                @if($corporate->contact_email)
                <div class="flex justify-between gap-2">
                    <dt class="text-slate-500 shrink-0">Email</dt>
                    <dd class="text-right text-xs">{{ $corporate->contact_email }}</dd>
                </div>
                @endif
                @if($corporate->contract_start || $corporate->contract_end)
                <div class="flex justify-between">
                    <dt class="text-slate-500">Contract</dt>
                    <dd class="text-xs text-right">
                        {{ $corporate->contract_start?->format('d M Y') ?? '—' }}
                        → {{ $corporate->contract_end?->format('d M Y') ?? 'Open' }}
                    </dd>
                </div>
                @endif
                @if($corporate->billing_terms)
                <div class="pt-1 border-t border-slate-100 dark:border-slate-700">
                    <dt class="text-slate-500 mb-1">Billing Terms</dt>
                    <dd class="text-xs text-slate-600 dark:text-slate-300">{{ $corporate->billing_terms }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Stats --}}
        <div class="card p-5">
            <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Booking Stats</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="text-center rounded-xl bg-slate-50 dark:bg-slate-700 p-3">
                    <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $stats['total_bookings'] }}</p>
                    <p class="text-xs text-slate-500">Total Bookings</p>
                </div>
                <div class="text-center rounded-xl bg-slate-50 dark:bg-slate-700 p-3">
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['active_bookings'] }}</p>
                    <p class="text-xs text-slate-500">Active Now</p>
                </div>
                <div class="col-span-2 text-center rounded-xl bg-navy/5 dark:bg-navy/20 p-3">
                    <p class="text-xl font-bold text-navy dark:text-navy-light">{{ money($stats['total_spend']) }}</p>
                    <p class="text-xs text-slate-500">Total Spend</p>
                </div>
                <div class="col-span-2 text-center rounded-xl bg-slate-50 dark:bg-slate-700 p-3">
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ money($stats['avg_booking']) }}</p>
                    <p class="text-xs text-slate-500">Avg. Booking Value</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: bookings table --}}
    <div class="lg:col-span-2">
        <div class="card p-5">
            <h3 class="font-bold text-slate-900 dark:text-white mb-4">Bookings by this Company</h3>

            @if($bookings->isEmpty())
            <div class="text-center py-12">
                <p class="text-slate-400 text-sm">No bookings yet. Share the portal link with the company!</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ref</th>
                            <th>Guest</th>
                            <th>Dates</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                        <tr>
                            <td class="font-mono text-xs">
                                <a href="{{ route('owner.bookings.show', $booking) }}" class="text-navy dark:text-navy-light hover:underline">
                                    {{ $booking->booking_number }}
                                </a>
                            </td>
                            <td>
                                <p class="font-medium text-sm">{{ $booking->user->name ?? 'Guest' }}</p>
                                <p class="text-xs text-slate-400">{{ $booking->user->email ?? '' }}</p>
                            </td>
                            <td class="text-sm">
                                {{ $booking->check_in->format('d M') }} – {{ $booking->check_out->format('d M Y') }}
                                <span class="text-xs text-slate-400">({{ $booking->nights }}n)</span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $booking->status === 'confirmed' ? 'confirmed' : ($booking->status === 'cancelled' ? 'cancelled' : ($booking->status === 'pending' ? 'pending' : 'confirmed')) }}">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="text-right font-semibold text-sm">{{ money($booking->grand_total) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($bookings->hasPages())
            <div class="mt-4">{{ $bookings->links() }}</div>
            @endif
            @endif
        </div>
    </div>
</div>

@endsection
