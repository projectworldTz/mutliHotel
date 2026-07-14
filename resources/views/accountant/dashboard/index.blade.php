@extends('layouts.accountant')
@section('title', __('Dashboard'))
@section('page-title', __('Accounts Dashboard'))

@section('content')

<div class="grid gap-4 sm:grid-cols-3 mb-6">
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Outstanding Invoices') }}</p>
        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['outstanding_invoices'] }}</p>
    </div>
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Revenue This Month') }}</p>
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ money($stats['revenue_this_month']) }}</p>
    </div>
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm p-5">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Refunded This Month') }}</p>
        <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ money($stats['refunded_this_month']) }}</p>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Recent Invoices') }}</h3>
            <a href="{{ route('accountant.invoices.index') }}" class="text-xs text-navy dark:text-gold hover:underline">{{ __('View all') }}</a>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @php
                $badgeColors = ['blue'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400','green'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400','red'=>'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400','purple'=>'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400','gray'=>'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300'];
            @endphp
            @forelse($recentInvoices as $invoice)
            <a href="{{ route('accountant.invoices.show', $invoice) }}" class="flex items-center justify-between px-5 py-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                <div class="min-w-0">
                    <p class="font-medium text-slate-900 dark:text-white">{{ $invoice->invoice_number }}</p>
                    <p class="text-xs text-slate-400">{{ $invoice->booking->user->name ?? __('Guest') }}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">{{ money($invoice->grand_total) }}</p>
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badgeColors[$invoice->status_badge['color']] ?? '' }}">{{ $invoice->status_badge['label'] }}</span>
                </div>
            </a>
            @empty
            <p class="px-5 py-8 text-center text-sm text-slate-400">{{ __('No invoices yet.') }}</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ __('Recent Expenses & Payouts') }}</h3>
            <a href="{{ route('accountant.expenses.index') }}" class="text-xs text-navy dark:text-gold hover:underline">{{ __('View all') }}</a>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse($recentExpenses as $expense)
            <div class="flex items-center justify-between px-5 py-3">
                <div class="min-w-0">
                    <p class="font-medium text-slate-900 dark:text-white">{{ $expense->description }}</p>
                    <p class="text-xs text-slate-400">{{ $expense->expense_date->format('d M Y') }} · {{ ucfirst($expense->type) }}</p>
                </div>
                <p class="font-semibold text-slate-700 dark:text-slate-200">{{ money($expense->amount) }}</p>
            </div>
            @empty
            <p class="px-5 py-8 text-center text-sm text-slate-400">{{ __('No expenses recorded yet.') }}</p>
            @endforelse
        </div>
    </div>
</div>

@endsection
