@extends('layouts.admin')
@section('title', 'Occupancy Report')
@section('page-title', 'Occupancy Report')

@section('content')
<div class="mb-5 flex gap-3">
    <a href="{{ route('admin.reports.revenue') }}" class="btn-outline btn-sm">Revenue</a>
    <a href="{{ route('admin.reports.occupancy') }}" class="btn-primary btn-sm">Occupancy</a>
</div>

<form method="GET" action="{{ route('admin.reports.occupancy') }}" class="mb-5 flex gap-2">
    <input type="date" name="from" value="{{ request('from', now()->startOfMonth()->toDateString()) }}" class="form-input py-2 text-sm w-auto">
    <input type="date" name="to" value="{{ request('to', now()->endOfMonth()->toDateString()) }}" class="form-input py-2 text-sm w-auto">
    <button type="submit" class="btn-primary btn-sm">Generate</button>
</form>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr><th>Hotel</th><th>Booked Nights</th><th>Total Available</th><th>Occupancy Rate</th></tr>
        </thead>
        <tbody>
            @foreach($report as $row)
            <tr class="tr-hover">
                <td class="font-medium">{{ $row['hotel'] }}</td>
                <td>{{ $row['booked_nights'] }}</td>
                <td>{{ $row['total_nights'] }}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                            <div class="bg-navy rounded-full h-2" style="width: {{ min($row['rate'], 100) }}%"></div>
                        </div>
                        <span class="text-sm font-semibold">{{ $row['rate'] }}%</span>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
