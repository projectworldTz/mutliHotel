@extends('layouts.accountant')
@section('title', __('Expenses'))
@section('page-title', __('Expenses & Payouts'))

@section('content')

<div class="grid gap-4 sm:grid-cols-2 mb-6">
    <div class="rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-white/60 dark:border-slate-700 p-4">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Total Expenses') }}</p>
        <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ money($summary['total_expenses']) }}</p>
    </div>
    <div class="rounded-2xl bg-purple-50 dark:bg-purple-900/20 border border-white/60 dark:border-slate-700 p-4">
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">{{ __('Total Payouts') }}</p>
        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ money($summary['total_payouts']) }}</p>
    </div>
</div>

<div class="mb-4 flex justify-end">
    <button x-data @click="$dispatch('open-add-expense')" class="btn-primary flex items-center gap-2">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('Add Entry') }}
    </button>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Payee') }}</th>
                <th>{{ __('Amount') }}</th>
                <th class="w-16"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenses as $expense)
            <tr class="tr-hover">
                <td class="text-sm text-slate-500">{{ $expense->expense_date->format('d M Y') }}</td>
                <td>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $expense->type === 'payout' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400' }}">
                        {{ ucfirst($expense->type) }}
                    </span>
                </td>
                <td class="font-medium text-slate-900 dark:text-white">{{ $expense->description }}</td>
                <td class="text-sm text-slate-500">{{ $expense->category ?? '—' }}</td>
                <td class="text-sm text-slate-500">{{ $expense->payee ?? '—' }}</td>
                <td class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ money($expense->amount) }}</td>
                <td>
                    <form method="POST" action="{{ route('accountant.expenses.destroy', $expense) }}"
                          onsubmit="return confirm('{{ __('Remove this entry?') }}')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="py-10 text-center text-slate-500">{{ __('No expenses recorded yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($expenses->hasPages())
    <div class="p-4 border-t border-slate-100 dark:border-slate-700">{{ $expenses->links() }}</div>
    @endif
</div>

{{-- ── Add Entry Modal ──────────────────────────────────────────────────────── --}}
<div x-data="{ open: false }"
     x-on:open-add-expense.window="open = true"
     x-show="open"
     x-trap="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ __('Add Entry') }}</h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('accountant.expenses.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">{{ __('Type') }} *</label>
                <select name="type" class="form-select" required>
                    <option value="expense">{{ __('Expense') }}</option>
                    <option value="payout">{{ __('Payout') }}</option>
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('Description') }} *</label>
                <input type="text" name="description" class="form-input" required maxlength="255">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('Category') }}</label>
                    <input type="text" name="category" class="form-input" placeholder="{{ __('Utilities, Supplies…') }}" maxlength="100">
                </div>
                <div>
                    <label class="form-label">{{ __('Payee') }}</label>
                    <input type="text" name="payee" class="form-input" maxlength="150">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">{{ __('Amount') }} *</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">{{ __('Date') }} *</label>
                    <input type="date" name="expense_date" value="{{ now()->toDateString() }}" class="form-input" required>
                </div>
            </div>
            <div>
                <label class="form-label">{{ __('Notes') }}</label>
                <textarea name="notes" rows="2" class="form-input"></textarea>
            </div>
            <button type="submit" class="btn-primary w-full">{{ __('Save Entry') }}</button>
        </form>
    </div>
</div>

@endsection
