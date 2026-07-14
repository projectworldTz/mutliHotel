@extends('layouts.accountant')
@section('title', __('Invoices'))
@section('page-title', __('Invoices & Payments'))

@section('content')

@php
    $badgeColors = ['blue'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400','green'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400','red'=>'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400','purple'=>'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400','gray'=>'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'];
@endphp

<form method="GET" class="mb-4 flex flex-wrap gap-2">
    <select name="status" class="form-input w-auto text-sm" onchange="this.form.submit()">
        <option value="">{{ __('All Statuses') }}</option>
        @foreach(['draft'=>'Draft','issued'=>'Issued','paid'=>'Paid','cancelled'=>'Cancelled','refunded'=>'Refunded'] as $v => $l)
        <option value="{{ $v }}" @selected(request('status') === $v)>{{ __($l) }}</option>
        @endforeach
    </select>
</form>

<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Invoice #') }}</th>
                    <th>{{ __('Guest') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Issued') }}</th>
                    <th class="w-16"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                <tr class="tr-hover">
                    <td class="font-mono text-xs text-slate-500">{{ $invoice->invoice_number }}</td>
                    <td class="font-medium text-slate-900 dark:text-white">{{ $invoice->booking->user->name ?? __('Guest') }}</td>
                    <td class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ money($invoice->grand_total) }}</td>
                    <td>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeColors[$invoice->status_badge['color']] ?? '' }}">{{ $invoice->status_badge['label'] }}</span>
                    </td>
                    <td class="text-sm text-slate-500">{{ $invoice->issued_at?->format('d M Y') ?? '—' }}</td>
                    <td>
                        <a href="{{ route('accountant.invoices.show', $invoice) }}" class="btn-ghost btn-sm">{{ __('View') }}</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-10 text-center text-slate-500">{{ __('No invoices found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $invoices->links() }}</div>
    @endif
</div>

@endsection
