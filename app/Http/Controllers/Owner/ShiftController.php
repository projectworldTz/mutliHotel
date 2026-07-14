<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\StaffShift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Hotel $hotel, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::STAFF_SCHEDULING), 403,
            'Staff Scheduling is not enabled for this hotel.'
        );

        $weekStart = $request->filled('week')
            ? \Carbon\Carbon::parse($request->week)->startOfWeek()
            : now()->startOfWeek();
        $weekEnd = $weekStart->copy()->endOfWeek();

        $shifts = StaffShift::forHotel($hotel->id)
            ->whereBetween('shift_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->with('user')
            ->orderBy('shift_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(fn ($s) => $s->shift_date->toDateString());

        $staff = $hotel->staff()->with('user')->where('active', true)->get();

        return view('owner.shifts.index', compact('hotel', 'shifts', 'staff', 'weekStart', 'weekEnd'));
    }

    public function store(Hotel $hotel, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::STAFF_SCHEDULING), 403);

        $data = $request->validate([
            'user_id'    => ['required', 'exists:users,id'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i', 'after:start_time'],
            'role'       => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:500'],
        ]);

        // Only staff actually assigned to this hotel can be scheduled.
        abort_unless(
            $hotel->staff()->where('user_id', $data['user_id'])->where('active', true)->exists(),
            422, 'That user is not active staff at this hotel.'
        );

        StaffShift::create($data + ['hotel_id' => $hotel->id, 'created_by' => auth()->id()]);

        return back()->with('success', 'Shift added.');
    }

    public function destroy(Hotel $hotel, StaffShift $shift)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($shift->hotel_id === $hotel->id, 403);

        $shift->delete();

        return back()->with('success', 'Shift removed.');
    }
}
