<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Repositories\AvailabilityRepository;
use App\Repositories\RoomRepository;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
    public function __construct(
        private RoomRepository        $roomRepository,
        private AvailabilityRepository $availabilityRepository,
        private PricingService        $pricingService,
    ) {}

    /**
     * Check availability and pricing for a specific room type.
     *
     * Returns:
     *   available      bool
     *   room           Room|null       — the assigned room (not yet locked)
     *   available_count int
     *   pricing        array           — nightly_rate, nights, total
     *   reason         string|null     — human-readable message when unavailable
     */
    public function checkRoomType(
        Hotel    $hotel,
        RoomType $roomType,
        string   $checkIn,
        string   $checkOut,
        int      $guests
    ): array {
        if ($guests > $roomType->max_guests) {
            return [
                'available'       => false,
                'room'            => null,
                'available_count' => 0,
                'pricing'         => [],
                'reason'          => "This room type accommodates up to {$roomType->max_guests} guests.",
            ];
        }

        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

        if ($nights < 1) {
            return [
                'available'       => false,
                'room'            => null,
                'available_count' => 0,
                'pricing'         => [],
                'reason'          => 'Check-out must be after check-in.',
            ];
        }

        $availableRooms = $this->roomRepository->availableRoomsForType($roomType, $checkIn, $checkOut);

        if ($availableRooms->isEmpty()) {
            $nextAvailable = $this->availabilityRepository->nextAvailableDateForType($roomType, $checkIn);

            return [
                'available'       => false,
                'room'            => null,
                'available_count' => 0,
                'pricing'         => [],
                'next_available'  => $nextAvailable,
                'reason'          => 'fully_booked',
            ];
        }

        $pricing = $this->pricingService->calculateForStay($roomType, $checkIn, $checkOut);

        return [
            'available'       => true,
            'room'            => $availableRooms->first(),
            'available_count' => $availableRooms->count(),
            'pricing'         => $pricing,
            'next_available'  => null,
            'reason'          => null,
        ];
    }

    /**
     * Return ALL room types for a hotel split into two groups:
     *   available   — have at least one free room, with count and pricing
     *   unavailable — fully booked for the requested dates, with next_available date
     */
    public function availableRoomTypes(Hotel $hotel, string $checkIn, string $checkOut, int $guests): array
    {
        $roomTypes   = $this->roomRepository->roomTypesForHotel($hotel);
        $available   = [];
        $unavailable = [];

        foreach ($roomTypes as $roomType) {
            $check = $this->checkRoomType($hotel, $roomType, $checkIn, $checkOut, $guests);
            $entry = array_merge(['room_type' => $roomType], $check);

            if ($check['available']) {
                $available[] = $entry;
            } else {
                $unavailable[] = $entry;
            }
        }

        // Cheapest available first
        usort($available, fn ($a, $b) => $a['pricing']['subtotal'] <=> $b['pricing']['subtotal']);

        return ['available' => $available, 'unavailable' => $unavailable];
    }

    /**
     * Build a calendar array for a room type for a given month.
     * Each day entry: ['date' => string, 'status' => available|booked|blocked]
     */
    public function calendarForRoomType(RoomType $roomType, int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $to   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $fullyBlocked = $this->availabilityRepository->fullyBlockedDatesForType(
            $roomType->id,
            $from,
            $to
        );
        $blockedSet = array_flip($fullyBlocked);

        $calendar = [];
        foreach (CarbonPeriod::create($from, $to) as $date) {
            $dateStr    = $date->toDateString();
            $isPast     = $date->isPast() && ! $date->isToday();
            $calendar[] = [
                'date'   => $dateStr,
                'status' => $isPast ? 'past' : (isset($blockedSet[$dateStr]) ? 'booked' : 'available'),
            ];
        }

        return $calendar;
    }

    /**
     * Build a per-room-level calendar for a room type for a given month, used by the
     * receptionist availability screen. Unlike calendarForRoomType() (which only flags a
     * date "booked" once every room of the type is sold out), this distinguishes a date
     * with some — but not all — rooms booked as "partial", so a single booking is visible.
     * Each day entry: ['date' => string, 'status' => available|partial|booked|past]
     */
    public function detailedCalendarForRoomType(RoomType $roomType, int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $to   = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $totalRooms    = $roomType->rooms()->available()->count();
        $blockedCounts = $this->availabilityRepository->blockedCountsForType($roomType->id, $from, $to);

        $calendar = [];
        foreach (CarbonPeriod::create($from, $to) as $date) {
            $dateStr = $date->toDateString();
            $isPast  = $date->isPast() && ! $date->isToday();
            $blocked = (int) ($blockedCounts[$dateStr] ?? 0);

            $status = match (true) {
                $isPast                        => 'past',
                $blocked === 0                 => 'available',
                $totalRooms > 0 && $blocked >= $totalRooms => 'booked',
                default                        => 'partial',
            };

            $calendar[] = ['date' => $dateStr, 'status' => $status];
        }

        return $calendar;
    }

    /**
     * Validate that a proposed stay window makes sense.
     * Returns ['valid' => bool, 'errors' => array].
     */
    public function validateDates(string $checkIn, string $checkOut): array
    {
        $errors  = [];
        $inDate  = Carbon::parse($checkIn);
        $outDate = Carbon::parse($checkOut);

        if ($inDate->isPast() && ! $inDate->isToday()) {
            $errors[] = 'Check-in date cannot be in the past.';
        }

        if (! $outDate->isAfter($inDate)) {
            $errors[] = 'Check-out must be after check-in.';
        }

        if ($inDate->diffInDays($outDate) > 90) {
            $errors[] = 'Maximum stay is 90 nights.';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }
}
