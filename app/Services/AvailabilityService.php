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
            return [
                'available'       => false,
                'room'            => null,
                'available_count' => 0,
                'pricing'         => [],
                'reason'          => 'No rooms are available for the selected dates.',
            ];
        }

        $pricing = $this->pricingService->calculateForStay($roomType, $checkIn, $checkOut);

        return [
            'available'       => true,
            'room'            => $availableRooms->first(),
            'available_count' => $availableRooms->count(),
            'pricing'         => $pricing,
            'reason'          => null,
        ];
    }

    /**
     * Return all room types for a hotel that have at least one available room
     * for the requested stay, enriched with availability count and pricing.
     */
    public function availableRoomTypes(Hotel $hotel, string $checkIn, string $checkOut, int $guests): array
    {
        $roomTypes = $this->roomRepository->roomTypesForHotel($hotel);
        $results   = [];

        foreach ($roomTypes as $roomType) {
            $check = $this->checkRoomType($hotel, $roomType, $checkIn, $checkOut, $guests);

            if ($check['available']) {
                $results[] = array_merge(['room_type' => $roomType], $check);
            }
        }

        // Sort cheapest first
        usort($results, fn ($a, $b) => $a['pricing']['subtotal'] <=> $b['pricing']['subtotal']);

        return $results;
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
