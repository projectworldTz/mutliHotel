<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds room types, rooms, and realistic booking history for the real
 * "LUXURY HOTEL" record created via the admin/owner panel.
 *
 * Run with:
 *   php artisan db:seed --class=LuxuryHotelSeeder
 */
class LuxuryHotelSeeder extends Seeder
{
    private const HOTEL_NAME = 'LUXURY HOTEL';
    private const TAX_RATE   = 0;   // %
    private const CURRENCY   = 'TZS';

    private int $bookingSeq = 1;

    // Populated in seedRoomTypesAndRooms(): room_id => [room_type_id, nightly_rate]
    private array $rooms = [];

    public function run(): void
    {
        \Illuminate\Database\Eloquent\Model::unguard();

        $hotel = Hotel::where('name', self::HOTEL_NAME)->first();

        if (! $hotel) {
            $this->command->error(
                'Hotel "' . self::HOTEL_NAME . '" not found. Check the exact name in the hotels table '
                . '(Admin > Hotels) and update the HOTEL_NAME constant in this seeder if it differs.'
            );
            return;
        }

        $this->seedRoomTypesAndRooms($hotel);

        if (empty($this->rooms)) {
            $this->command->error('No rooms available for this hotel — aborting booking seed.');
            return;
        }

        $customers = $this->seedCustomers();
        $this->seedBookings($hotel, $customers);

        \Illuminate\Database\Eloquent\Model::reguard();

        $this->command->info('LuxuryHotelSeeder complete.');
    }

    // ── Room types & rooms ───────────────────────────────────────────────────

    private function seedRoomTypesAndRooms(Hotel $hotel): void
    {
        $types = RoomType::where('hotel_id', $hotel->id)->get();

        if ($types->isEmpty()) {
            $typeDefs = [
                ['name' => 'Standard Room',      'slug' => 'standard-room',      'base_price' => 60000,  'max_guests' => 2, 'bed_type' => 'twin',  'beds_count' => 2, 'size_sqm' => 22, 'view_type' => 'Garden View'],
                ['name' => 'Deluxe Room',        'slug' => 'deluxe-room',        'base_price' => 120000, 'max_guests' => 2, 'bed_type' => 'queen', 'beds_count' => 1, 'size_sqm' => 34, 'view_type' => 'City View'],
                ['name' => 'Executive Suite',    'slug' => 'executive-suite',    'base_price' => 250000, 'max_guests' => 3, 'bed_type' => 'king',  'beds_count' => 1, 'size_sqm' => 54, 'view_type' => 'Ocean View'],
                ['name' => 'Presidential Suite', 'slug' => 'presidential-suite', 'base_price' => 450000, 'max_guests' => 4, 'bed_type' => 'king',  'beds_count' => 2, 'size_sqm' => 88, 'view_type' => 'Panoramic View'],
            ];

            foreach ($typeDefs as $t) {
                RoomType::create([
                    'hotel_id'    => $hotel->id,
                    'name'        => $t['name'],
                    'slug'        => $t['slug'],
                    'description' => "{$t['name']} at {$hotel->name}.",
                    'base_price'  => $t['base_price'],
                    'max_guests'  => $t['max_guests'],
                    'bed_type'    => $t['bed_type'],
                    'beds_count'  => $t['beds_count'],
                    'size_sqm'    => $t['size_sqm'],
                    'view_type'   => $t['view_type'],
                    'smoking'     => false,
                    'status'      => 'active',
                ]);
                $this->command->line("  \u{2713} Room type: {$t['name']}");
            }

            $types = RoomType::where('hotel_id', $hotel->id)->get();
        }

        $typesBySlug = $types->keyBy('slug');

        $roomPlan = [
            'standard-room'      => range(101, 110),
            'deluxe-room'        => range(201, 208),
            'executive-suite'    => range(301, 306),
            'presidential-suite' => range(401, 403),
        ];

        $created = 0;
        foreach ($roomPlan as $slug => $numbers) {
            $type = $typesBySlug[$slug] ?? null;
            if (! $type) {
                continue;
            }

            foreach ($numbers as $num) {
                $room = Room::firstOrCreate(
                    ['hotel_id' => $hotel->id, 'room_number' => (string) $num],
                    [
                        'room_type_id' => $type->id,
                        'floor'        => (int) substr((string) $num, 0, 1),
                        'status'       => 'available',
                    ]
                );
                $created += $room->wasRecentlyCreated ? 1 : 0;
            }
        }

        $this->command->line("  \u{2713} Rooms ready ({$created} newly created).");

        $rooms = Room::where('hotel_id', $hotel->id)->with('roomType')->get();
        foreach ($rooms as $room) {
            if ($room->roomType) {
                $this->rooms[$room->id] = [$room->room_type_id, (float) $room->roomType->base_price];
            }
        }
    }

    // ── Customers ────────────────────────────────────────────────────────────

    private function seedCustomers(): array
    {
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $names = [
            'Alice Mwangi', 'Brian Oduya', 'Carol Ndege', 'David Kamau',
            'Eve Mutua', 'Frank Otieno', 'Grace Wanjiru', 'Henry Kipchoge',
            'Irene Achieng', 'James Maina', 'Karen Njeri', 'Leo Omondi',
            'Mary Wambui', 'Nick Osei', 'Olivia Hamisi',
        ];

        $users = [];
        foreach ($names as $name) {
            $slug = Str::slug($name);
            $user = User::firstOrCreate(
                ['email' => "{$slug}@example.com"],
                ['name' => $name, 'password' => Hash::make('password123')]
            );
            if (! $user->roles->contains('id', $customerRole->id)) {
                $user->roles()->attach($customerRole->id);
            }
            $users[] = $user;
        }

        $this->command->line('  ' . "\u{2713}" . ' Customer accounts ready.');
        return $users;
    }

    // ── Bookings ─────────────────────────────────────────────────────────────

    private function seedBookings(Hotel $hotel, array $customers): void
    {
        $roomIds = array_keys($this->rooms);

        // [status, check_in_offset_days, nights, count]
        $scenarios = [
            ['checked_out', -180, 2, 5],
            ['checked_out', -150, 3, 5],
            ['checked_out', -120, 2, 4],
            ['checked_out', -90,  4, 4],
            ['checked_out', -60,  2, 4],
            ['cancelled',   -140, 2, 3],
            ['cancelled',   -100, 3, 3],
            ['cancelled',   -40,  2, 2],
            ['cancelled',   -15,  2, 2],
            ['refunded',    -80,  3, 3],
            ['no_show',     -50,  2, 2],
            ['no_show',     -30,  1, 2],
            ['confirmed',   +5,   3, 5],
            ['confirmed',   +15,  2, 4],
            ['confirmed',   +30,  4, 3],
            ['checked_in',  -1,   3, 4],
            ['pending',     +3,   2, 5],
        ];

        $count = 0;
        foreach ($scenarios as [$status, $offset, $nights, $times]) {
            for ($i = 0; $i < $times; $i++) {
                $roomId   = $roomIds[array_rand($roomIds)];
                $user     = $customers[array_rand($customers)];
                $checkIn  = Carbon::today()->addDays($offset + $i)->toDateString();
                $checkOut = Carbon::parse($checkIn)->addDays($nights)->toDateString();

                $this->createBooking($hotel, $user, $roomId, $checkIn, $checkOut, $nights, $status);
                $count++;
            }
        }

        $this->command->line("  \u{2713} {$count} bookings created for {$hotel->name}.");
    }

    private function createBooking(Hotel $hotel, User $user, int $roomId, string $checkIn, string $checkOut, int $nights, string $status): void
    {
        [$roomTypeId, $rate] = $this->rooms[$roomId];

        $subtotal  = $rate * $nights;
        $taxTotal  = round($subtotal * self::TAX_RATE / 100);
        $grand     = $subtotal + $taxTotal;
        $createdAt = Carbon::parse($checkIn)->subDays(rand(3, 30));

        $booking = Booking::create([
            'booking_number'               => 'BK-LUX-' . str_pad((string) $this->bookingSeq++, 5, '0', STR_PAD_LEFT),
            'user_id'                      => $user->id,
            'hotel_id'                     => $hotel->id,
            'status'                       => $status,
            'check_in'                     => $checkIn,
            'check_out'                    => $checkOut,
            'nights'                       => $nights,
            'guests_adults'                => rand(1, 2),
            'guests_children'              => rand(0, 1),
            'sub_total'                    => $subtotal,
            'tax_total'                    => $taxTotal,
            'tax_rate'                     => self::TAX_RATE,
            'discount_total'               => 0,
            'grand_total'                  => $grand,
            'currency'                     => self::CURRENCY,
            'cancellation_policy_snapshot' => json_encode(['type' => 'moderate']),
            'confirmed_at'                 => in_array($status, ['confirmed', 'checked_in', 'checked_out']) ? $createdAt->copy()->addHours(1) : null,
            'checked_in_at'                => in_array($status, ['checked_in', 'checked_out']) ? Carbon::parse($checkIn)->setHour(14) : null,
            'checked_out_at'               => $status === 'checked_out' ? Carbon::parse($checkOut)->setHour(11) : null,
            'cancelled_at'                 => in_array($status, ['cancelled', 'refunded']) ? $createdAt->copy()->addDays(rand(1, 3)) : null,
            'created_at'                   => $createdAt,
            'updated_at'                   => $createdAt,
        ]);

        BookingRoom::create([
            'booking_id'   => $booking->id,
            'room_id'      => $roomId,
            'room_type_id' => $roomTypeId,
            'check_in'     => $checkIn,
            'check_out'    => $checkOut,
            'nightly_rate' => $rate,
            'nights'       => $nights,
            'sub_total'    => $subtotal,
        ]);

        if (in_array($status, ['confirmed', 'checked_in', 'pending'])) {
            $day = Carbon::parse($checkIn);
            while ($day->lt(Carbon::parse($checkOut))) {
                DB::table('room_availability')->insertOrIgnore([
                    'room_id'    => $roomId,
                    'booking_id' => $booking->id,
                    'date'       => $day->toDateString(),
                    'status'     => 'blocked',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $day->addDay();
            }
        }

        $payMethod = ['airtel_money', 'mpesa', 'halotel', 'mix_by_yas'][rand(0, 3)];
        $payStatus = match ($status) {
            'pending'   => 'pending',
            'cancelled' => 'failed',
            'refunded'  => 'refunded',
            default     => 'confirmed',
        };
        Payment::create([
            'booking_id'     => $booking->id,
            'order_id'       => rand(100000, 999999),
            'method'         => $payMethod,
            'status'         => $payStatus,
            'transaction_id' => $payStatus === 'pending' ? null : 'TXN-' . strtoupper(Str::random(12)),
            'amount'         => $grand,
            'refund_amount'  => $status === 'refunded' ? $grand : 0,
            'currency'       => self::CURRENCY,
            'created_at'     => $createdAt,
            'updated_at'     => $createdAt,
        ]);

        $invStatus = match ($status) {
            'pending'               => 'pending',
            'cancelled', 'refunded' => 'cancelled',
            default                 => 'paid',
        };
        Invoice::create([
            'booking_id'     => $booking->id,
            'invoice_number' => 'INV-' . $booking->booking_number,
            'subtotal'       => $subtotal,
            'tax_total'      => $taxTotal,
            'discount_total' => 0,
            'grand_total'    => $grand,
            'refund_amount'  => $status === 'refunded' ? $grand : 0,
            'currency'       => self::CURRENCY,
            'status'         => $invStatus,
            'issued_at'      => $createdAt,
            'due_at'         => $createdAt->copy()->addDays(1),
            'paid_at'        => $invStatus === 'paid' ? $createdAt->copy()->addHours(2) : null,
            'created_at'     => $createdAt,
            'updated_at'     => $createdAt,
        ]);
    }
}
