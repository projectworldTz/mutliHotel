@extends('layouts.accountant')
@section('title', $invoice->invoice_number)
@section('page-title', $invoice->invoice_number)

@section('content')

@php
    $badgeColors = ['blue'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400','green'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400','red'=>'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400','purple'=>'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400','gray'=>'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'];
@endphp

<div class="mb-4"><a href="{{ route('accountant.invoices.index') }}" class="btn-ghost btn-sm">{{ __('← Invoices') }}</a></div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 card p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $invoice->invoice_number }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('Booking') }} {{ $invoice->booking->booking_number }}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeColors[$invoice->status_badge['color']] ?? '' }}">{{ $invoice->status_badge['label'] }}</span>
        </div>

        <dl class="space-y-2 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Guest') }}</dt><dd class="font-medium text-slate-900 dark:text-white">{{ $invoice->booking->user->name ?? '—' }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Subtotal') }}</dt><dd>{{ money($invoice->subtotal) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Tax') }}</dt><dd>{{ money($invoice->tax_total) }}</dd></div>
            <div class="flex justify-between"><dt class="text-slate-500">{{ __('Discount') }}</dt><dd>-{{ money($invoice->discount_total) }}</dd></div>
            <div class="flex justify-between text-base font-bold border-t border-slate-100 dark:border-slate-700 pt-2 mt-2">
                <dt>{{ __('Grand Total') }}</dt><dd>{{ money($invoice->grand_total) }}</dd>
            </div>
            @if($invoice->status === 'refunded')
            <div class="flex justify-between text-rose-600 dark:text-rose-400"><dt>{{ __('Refunded') }}</dt><dd>{{ money($invoice->refund_amount) }}</dd></div>
            @endif
        </dl>
    </div>

    <div class="space-y-4">
        @if(!in_array($invoice->status, ['paid', 'refunded', 'cancelled']))
        <form method="POST" action="{{ route('accountant.invoices.mark-paid', $invoice) }}" class="card p-5">
            @csrf
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-3">{{ __('Confirm payment has been received for this invoice.') }}</p>
            <button type="submit" class="btn-primary w-full">{{ __('Mark as Paid') }}</button>
        </form>
        @endif

        @if(!in_array($invoice->status, ['refunded', 'cancelled']))
        <form method="POST" action="{{ route('accountant.invoices.refund', $invoice) }}" class="card p-5 space-y-3">
            @csrf
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ __('Issue Refund') }}</p>
            <div>
                <label class="form-label">{{ __('Amount') }}</label>
                <input type="number" step="0.01" min="0.01" max="{{ $invoice->grand_total }}" name="amount"
                       value="{{ $invoice->grand_total }}" class="form-input" required>
            </div>
            <div>
                <label class="form-label">{{ __('Reason') }} <span class="font-normal text-slate-400">({{ __('optional') }})</span></label>
                <textarea name="reason" rows="2" class="form-input"></textarea>
            </div>
            <button type="submit" class="btn-danger w-full"
                    onclick="return confirm('{{ __('Issue this refund? This cannot be undone.') }}')">
                {{ __('Issue Refund') }}
            </button>
        </form>
        @endif
    </div>
</div>

@endsection
