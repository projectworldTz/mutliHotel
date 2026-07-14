<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\HotelVisit;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelReportService
{
    /**
     * Build the full analytics/report data set for a hotel over a given
     * trailing period (in days). Shared by the owner's Analytics page and
     * the accountant's Financial Reports page so both see identical numbers.
     */
    public function buildReport(Hotel $hotel, int $period): array
    {
        $period   = in_array($period, [30, 90, 365]) ? $period : 30;
        $from     = now()->subDays($period)->startOfDay();
        $to       = now()->endOfDay();
        $prevFrom = now()->subDays($period * 2)->startOfDay();
        $prevTo   = $from->copy()->subSecond();

        $paid = [
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_CHECKED_IN,
            Booking::STATUS_CHECKED_OUT,
        ];

        // ── Total room inventory ──────────────────────────────────────────────
        $totalRooms = Room::whereHas('roomType', fn ($q) => $q->where('hotel_id', $hotel->id))->count();

        // ── Current period base query ─────────────────────────────────────────
        $base = fn () => Booking::forHotel($hotel->id)
            ->whereIn('status', $paid)
            ->whereBetween('created_at', [$from, $to]);

        $prevBase = fn () => Booking::forHotel($hotel->id)
            ->whereIn('status', $paid)
            ->whereBetween('created_at', [$prevFrom, $prevTo]);

        // ── KPI: Revenue ──────────────────────────────────────────────────────
        $revenue     = (float) $base()->sum('grand_total');
        $prevRevenue = (float) $prevBase()->sum('grand_total');

        // ── KPI: Bookings count ───────────────────────────────────────────────
        $bookingsCount     = $base()->count();
        $prevBookingsCount = $prevBase()->count();

        // ── KPI: Nights sold (sum of nights column) ───────────────────────────
        $nightsSold     = (int) $base()->sum('nights');
        $prevNightsSold = (int) $prevBase()->sum('nights');

        // ── KPI: Average Length of Stay ───────────────────────────────────────
        $avgStay     = $bookingsCount > 0 ? round($nightsSold / $bookingsCount, 1) : 0;
        $prevAvgStay = $prevBookingsCount > 0 ? round($prevNightsSold / $prevBookingsCount, 1) : 0;

        // ── KPI: ADR (Average Daily Rate) ─────────────────────────────────────
        $adr     = $nightsSold > 0 ? round($revenue / $nightsSold, 2) : 0;
        $prevAdr = $prevNightsSold > 0 ? round($prevRevenue / $prevNightsSold, 2) : 0;

        // ── KPI: Occupancy Rate ───────────────────────────────────────────────
        $availableNights     = $totalRooms * $period;
        $prevAvailableNights = $totalRooms * $period;
        $occupancy     = $availableNights > 0 ? round(($nightsSold / $availableNights) * 100, 1) : 0;
        $prevOccupancy = $prevAvailableNights > 0 ? round(($prevNightsSold / $prevAvailableNights) * 100, 1) : 0;

        // ── KPI: RevPAR ───────────────────────────────────────────────────────
        $revpar     = $availableNights > 0 ? round($revenue / $availableNights, 2) : 0;
        $prevRevpar = $prevAvailableNights > 0 ? round($prevRevenue / $prevAvailableNights, 2) : 0;

        // ── KPI: Page Visits ──────────────────────────────────────────────────
        $visitsBase = fn ($f, $t) => HotelVisit::where('hotel_id', $hotel->id)
            ->whereBetween('visited_on', [$f->toDateString(), $t->toDateString()]);

        $visitsCount     = $visitsBase($from, $to)->count();
        $prevVisitsCount = $visitsBase($prevFrom, $prevTo)->count();

        // ── KPI: Cancellation Rate ────────────────────────────────────────────
        $totalAll    = Booking::forHotel($hotel->id)->whereBetween('created_at', [$from, $to])->count();
        $cancelled   = Booking::forHotel($hotel->id)->where('status', Booking::STATUS_CANCELLED)->whereBetween('created_at', [$from, $to])->count();
        $cancelRate  = $totalAll > 0 ? round(($cancelled / $totalAll) * 100, 1) : 0;

        // ── Revenue trend (daily or monthly based on period) ──────────────────
        if ($period <= 30) {
            $revenueTrend = Booking::forHotel($hotel->id)
                ->whereIn('status', $paid)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('DATE(created_at) as label, SUM(grand_total) as total, COUNT(*) as bookings')
                ->groupByRaw('DATE(created_at)')
                ->orderByRaw('DATE(created_at)')
                ->get()
                ->keyBy('label'); // keyed by YYYY-MM-DD — no re-parsing needed

            $trendLabels   = collect();
            $trendData     = collect();
            $bookingsTrend = collect();

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $key = $d->format('Y-m-d');
                $trendLabels->push($d->format('M d'));
                $trendData->push((float) ($revenueTrend->get($key)?->total ?? 0));
                $bookingsTrend->push((int) ($revenueTrend->get($key)?->bookings ?? 0));
            }
        } else {
            $revenueTrend = Booking::forHotel($hotel->id)
                ->whereIn('status', $paid)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as label, SUM(grand_total) as total, COUNT(*) as bookings')
                ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                ->orderByRaw('DATE_FORMAT(created_at, "%Y-%m")')
                ->get();
            $rawMonthLabels = $revenueTrend->pluck('label');
            $trendLabels    = $rawMonthLabels->map(fn ($l) => Carbon::parse($l . '-01')->format('M Y'));
            $trendData      = $revenueTrend->pluck('total')->map(fn ($v) => (float) $v);
            $bookingsTrend  = $revenueTrend->pluck('bookings')->map(fn ($v) => (int) $v);
        }

        // ── Visits trend (daily or monthly based on period) ────────────────────
        if ($period <= 30) {
            $visitsByDay = HotelVisit::where('hotel_id', $hotel->id)
                ->whereBetween('visited_on', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('visited_on as label, COUNT(*) as total')
                ->groupBy('visited_on')
                ->pluck('total', 'label');

            $visitsTrend = collect();
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $visitsTrend->push((int) ($visitsByDay->get($d->format('Y-m-d')) ?? 0));
            }
        } else {
            $visitsByMonth = HotelVisit::where('hotel_id', $hotel->id)
                ->whereBetween('visited_on', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('DATE_FORMAT(visited_on, "%Y-%m") as label, COUNT(*) as total')
                ->groupByRaw('DATE_FORMAT(visited_on, "%Y-%m")')
                ->pluck('total', 'label');

            $visitsTrend = $rawMonthLabels->map(fn ($l) => (int) ($visitsByMonth->get($l) ?? 0))->values();
        }

        // Day-over-day (or month-over-month) percent variation, for the trend chart.
        $visitsVariation = collect();
        $prevVisitValue  = null;
        foreach ($visitsTrend as $value) {
            $visitsVariation->push(match (true) {
                $prevVisitValue === null => null,
                $prevVisitValue > 0      => round((($value - $prevVisitValue) / $prevVisitValue) * 100, 1),
                $value > 0               => 100.0,
                default                  => 0.0,
            });
            $prevVisitValue = $value;
        }

        // ── Room type performance ─────────────────────────────────────────────
        $roomTypePerf = RoomType::where('hotel_id', $hotel->id)
            ->withCount('rooms')
            ->get()
            ->map(function ($rt) use ($hotel, $paid, $from, $to, $period) {
                $stats = DB::table('bookings')
                    ->join('booking_rooms', 'bookings.id', '=', 'booking_rooms.booking_id')
                    ->where('bookings.hotel_id', $hotel->id)
                    ->where('booking_rooms.room_type_id', $rt->id)
                    ->whereIn('bookings.status', $paid)
                    ->whereBetween('bookings.created_at', [$from, $to])
                    ->selectRaw('SUM(bookings.grand_total) as revenue, SUM(booking_rooms.nights) as nights, COUNT(DISTINCT bookings.id) as bookings')
                    ->first();

                $nights      = (int) ($stats->nights ?? 0);
                $roomNights  = $rt->rooms_count * $period;
                $occupancy   = $roomNights > 0 ? round(($nights / $roomNights) * 100, 1) : 0;
                $revenue     = (float) ($stats->revenue ?? 0);

                return [
                    'name'      => $rt->name,
                    'rooms'     => $rt->rooms_count,
                    'bookings'  => (int) ($stats->bookings ?? 0),
                    'nights'    => $nights,
                    'revenue'   => $revenue,
                    'occupancy' => $occupancy,
                    'adr'       => $nights > 0 ? round($revenue / $nights, 2) : 0,
                ];
            })
            ->sortByDesc('revenue')
            ->values();

        // ── Booking status breakdown ──────────────────────────────────────────
        $statusBreakdown = Booking::forHotel($hotel->id)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Top months / days (booking patterns) ─────────────────────────────
        $busyDays = Booking::forHotel($hotel->id)
            ->whereIn('status', $paid)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DAYNAME(check_in) as day_name, COUNT(*) as count')
            ->groupByRaw('DAYNAME(check_in), DAYOFWEEK(check_in)')
            ->orderByRaw('DAYOFWEEK(check_in)')
            ->pluck('count', 'day_name');

        // Build helpers for percent change
        $pct = fn ($now, $prev) => $prev > 0 ? round((($now - $prev) / $prev) * 100, 1) : null;

        $kpis = [
            ['label' => 'Total Revenue',       'value' => number_format($revenue, 2),       'prefix' => config('app.currency') . ' ', 'suffix' => '',  'change' => $pct($revenue, $prevRevenue),       'color' => 'emerald'],
            ['label' => 'Bookings',            'value' => $bookingsCount,                   'prefix' => '',  'suffix' => '',  'change' => $pct($bookingsCount, $prevBookingsCount), 'color' => 'blue'],
            ['label' => 'Page Visits',         'value' => $visitsCount,                     'prefix' => '',  'suffix' => '',  'change' => $pct($visitsCount, $prevVisitsCount), 'color' => 'indigo'],
            ['label' => 'Occupancy Rate',      'value' => $occupancy,                       'prefix' => '',  'suffix' => '%', 'change' => $pct($occupancy, $prevOccupancy),   'color' => 'purple'],
            ['label' => 'ADR',                 'value' => number_format($adr, 2),           'prefix' => config('app.currency') . ' ', 'suffix' => '',  'change' => $pct($adr, $prevAdr),             'color' => 'amber'],
            ['label' => 'RevPAR',              'value' => number_format($revpar, 2),        'prefix' => config('app.currency') . ' ', 'suffix' => '',  'change' => $pct($revpar, $prevRevpar),        'color' => 'rose'],
            ['label' => 'Avg. Stay',           'value' => $avgStay,                         'prefix' => '',  'suffix' => 'n', 'change' => $pct($avgStay, $prevAvgStay),     'color' => 'slate'],
        ];

        return compact(
            'hotel', 'period', 'kpis',
            'trendLabels', 'trendData', 'bookingsTrend', 'visitsTrend', 'visitsVariation',
            'roomTypePerf',
            'statusBreakdown',
            'busyDays',
            'cancelRate', 'totalRooms'
        );
    }
}
