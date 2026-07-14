@extends('layouts.owner')
@section('title', 'Group Bookings — ' . $hotel->name)
@section('page-title', 'Group Booking Manager')

@section('content')

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <div>
        <a href="{{ route('owner.hotels.show', $hotel) }}" class="text-sm text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">← {{ $hotel->name }}</a>
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mt-0.5">Group Booking Manager</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Track block reservations for conferences, weddings, and events.</p>
    </div>
    <button x-data @click="$dispatch('open-add-group-booking')" class="btn-primary flex items-center gap-2">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Add Event
    </button>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm overflow-hidden">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Event</th><th>Organizer</th><th>Dates</th><th>Rooms</th><th>Status</th><th class="w-20"></th></tr>
            </thead>
            <tbody>
                @forelse($groupBookings as $gb)
                @php $statusColors = ['inquiry'=>'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400','confirmed'=>'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400','completed'=>'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400','cancelled'=>'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400']; @endphp
                <tr class="tr-hover" x-data="{ editOpen: false }">
                    <td class="font-medium text-slate-900 dark:text-white">{{ $gb->event_name }}</td>
                    <td class="text-sm text-slate-600 dark:text-slate-300">
                        {{ $gb->organizer_name }}
                        @if($gb->organizer_email)<br><span class="text-xs text-slate-400">{{ $gb->organizer_email }}</span>@endif
                    </td>
                    <td class="text-sm text-slate-500">{{ $gb->event_start->format('d M') }} – {{ $gb->event_end->format('d M Y') }}</td>
                    <td class="text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $gb->rooms_requested }}</td>
                    <td><span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusColors[$gb->status] }}">{{ ucfirst($gb->status) }}</span></td>
                    <td>
                        <div class="flex items-center gap-1">
                            <button @click="editOpen = true" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('owner.group-bookings.destroy', [$hotel, $gb]) }}" onsubmit="return confirm('Delete {{ addslashes($gb->event_name) }}?')">
                                @csrf @method('DELETE')
                                <button class="rounded-lg p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>

                        <div x-show="editOpen" x-trap="editOpen" @click.outside="editOpen = false"
                             class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
                            <div class="absolute inset-0 bg-black/50" @click="editOpen = false"></div>
                            <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
                                <div class="flex items-center justify-between mb-5">
                                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Group Booking</h3>
                                    <button @click="editOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                @include('owner.group-bookings._form', ['groupBooking' => $gb, 'hotel' => $hotel, 'action' => route('owner.group-bookings.update', [$hotel, $gb]), 'method' => 'PUT'])
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-14 text-center text-slate-500">No group bookings tracked yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── Add Event Modal ──────────────────────────────────────────────────────── --}}
<div x-data="{ open: false }" x-on:open-add-group-booking.window="open = true" x-show="open" x-trap="open"
     class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display:none">
    <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-800 shadow-2xl p-6 z-10 max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Add Group Booking</h3>
            <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @include('owner.group-bookings._form', ['groupBooking' => null, 'hotel' => $hotel, 'action' => route('owner.group-bookings.store', $hotel), 'method' => 'POST'])
    </div>
</div>

@endsection
