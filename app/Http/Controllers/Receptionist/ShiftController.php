<?php

namespace App\Http\Controllers\Receptionist;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\StaffShift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($hotel->hasFeature(Feature::STAFF_SCHEDULING), 403,
            'Staff Scheduling is not enabled for this hotel. Contact your hotel owner.'
        );

        $shifts = StaffShift::forHotel($hotel->id)
            ->forUser(auth()->id())
            ->upcoming()
            ->orderBy('shift_date')
            ->orderBy('start_time')
            ->get();

        return view('receptionist.shifts.index', compact('hotel', 'shifts'));
    }
}
