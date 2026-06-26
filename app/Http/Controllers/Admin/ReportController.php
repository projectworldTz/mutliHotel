<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    public function revenue(Request $request)
    {
        $months = (int) $request->input('months', 12);
        $stats  = $this->bookingService->platformStats();

        // Raw rows from repository: [{year, month, total}, ...]
        $raw = $this->bookingService->revenueByMonth($months);

        // Enrich each row with a formatted label and booking count
        $report = collect($raw)->map(function (array $row) {
            $date = Carbon::createFromDate($row['year'], $row['month'], 1);

            $bookings = Booking::whereIn('status', [
                    Booking::STATUS_CONFIRMED,
                    Booking::STATUS_CHECKED_IN,
                    Booking::STATUS_CHECKED_OUT,
                ])
                ->whereYear('created_at', $row['year'])
                ->whereMonth('created_at', $row['month'])
                ->count();

            return [
                'month'    => $date->format('M Y'),  // "Jun 2026"
                'year'     => $row['year'],
                'total'    => $row['total'],
                'bookings' => $bookings,
            ];
        })->all();

        return view('admin.reports.revenue', compact('report', 'stats', 'months'));
    }

    public function occupancy(Request $request)
    {
        $hotels = Hotel::where('status', 'active')->with('roomTypes')->get();
        $from   = $request->input('from', now()->startOfMonth()->toDateString());
        $to     = $request->input('to', now()->endOfMonth()->toDateString());

        // Occupancy: booked nights / total available room-nights in date range
        $report = $hotels->map(function (Hotel $hotel) use ($from, $to) {
            $totalRooms      = $hotel->rooms()->count();
            $nights          = Carbon::parse($from)->diffInDays(Carbon::parse($to)) + 1;
            $totalRoomNights = $totalRooms * $nights;

            $bookedNights = \App\Models\RoomAvailability::whereHas('room', fn ($q) => $q->where('hotel_id', $hotel->id))
                ->whereIn('status', ['booked', 'blocked'])
                ->whereBetween('date', [$from, $to])
                ->count();

            return [
                'hotel'         => $hotel->name,
                'booked_nights' => $bookedNights,
                'total_nights'  => $totalRoomNights,
                'rate'          => $totalRoomNights > 0 ? round(($bookedNights / $totalRoomNights) * 100, 1) : 0,
            ];
        })->all();

        return view('admin.reports.occupancy', compact('report', 'from', 'to'));
    }
}
