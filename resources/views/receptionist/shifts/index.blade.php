@extends('layouts.receptionist')
@section('title', __('My Shifts'))
@section('page-title', __('My Shifts'))

@section('content')

<div class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>{{ __('Date') }}</th><th>{{ __('Time') }}</th><th>{{ __('Role') }}</th><th>{{ __('Notes') }}</th></tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                <tr class="tr-hover">
                    <td class="font-medium text-slate-900 dark:text-white">{{ $shift->shift_date->format('D, d M Y') }}</td>
                    <td class="text-sm">{{ $shift->time_range }}</td>
                    <td class="text-sm text-slate-600 dark:text-slate-300">{{ $shift->role ?? '—' }}</td>
                    <td class="text-sm text-slate-500">{{ $shift->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-14 text-center text-slate-500">{{ __('No upcoming shifts scheduled.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
