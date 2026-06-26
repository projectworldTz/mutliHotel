@extends('layouts.receptionist')
@section('title', 'Guests')
@section('page-title', 'Guests')

@section('content')
<div class="mb-5 flex items-center justify-between gap-3">
    <form method="GET" action="{{ route('receptionist.guests.index') }}" class="flex gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email or phone"
               class="form-input w-64">
        <button type="submit" class="btn-outline btn-sm">Search</button>
        @if($search)
            <a href="{{ route('receptionist.guests.index') }}" class="btn-ghost btn-sm">Clear</a>
        @endif
    </form>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Guest</th>
                <th>Phone</th>
                <th>Total Stays</th>
                <th>Member Since</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($guests as $guest)
            <tr class="tr-hover">
                <td>
                    <p class="font-medium text-slate-900 dark:text-white">{{ $guest->name }}</p>
                    <p class="text-xs text-slate-500">{{ $guest->email }}</p>
                </td>
                <td>{{ $guest->phone ?? '—' }}</td>
                <td>{{ $guest->total_stays }}</td>
                <td>{{ $guest->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('receptionist.guests.show', $guest) }}" class="btn-ghost btn-sm">View History</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="py-10 text-center text-slate-500">No guests found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $guests->links() }}</div>
@endsection
