<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Hotel;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds ~27 bookings across every status for the real "tranquiloo" hotel,
 * using its actual room/room-type records — safe to run against any
 * environment (including production) since nothing is hardcoded by ID.
 *
 *   php artisan db:seed --class=TranquilooBookingSeeder --force
 */
class TranquilooBookingSeeder extends Seeder
{
    private const HOTEL_SLUG = 'tranquiloo';
    private const TAX_RATE   = 18; // %
    private const CURRENCY   = 'TZS';

    public function run(): void
    {
        $hotel = Hotel::where('slug', self::HOTEL_SLUG)->first();

        if (! $hotel) {
            $this->command->error('No hotel with slug "' . self::HOTEL_SLUG . '" found — create it first.');
            return;
        }

        $rooms = Room::where('hotel_id', $hotel->id)->with('roomType')->get()
            ->filter(fn ($r) => $r->roomType !== null)
            ->values();

        if ($rooms->isEmpty()) {
            $this->command->error("\"{$hotel->name}\" has no rooms yet — add room types and rooms via the owner dashboard first.");
            return;
        }

        $customers = $this->seedCustomers();

        // [status, check_in_offset_days, nights, count]
        $scenarios = [
            ['checked_out', -60, 3, 5],
            ['checked_out', -30, 2, 4],
            ['cancelled',   -45, 2, 4],
            ['refunded',    -20, 3, 2],
            ['no_show',     -25, 1, 3],
            ['checked_in',  -1,  3, 3],
            ['confirmed',   +7,  2, 4],
            ['confirmed',   +21, 4, 2],
            ['pending',     +3,  2, 3],
            ['pending',     +14, 3, 2],
        ];

        $count = 0;
        foreach ($scenarios as [$status, $offset, $nights, $times]) {
            for ($i = 0; $i < $times; $i++) {
                $room     = $rooms[array_rand($rooms->all())];
                $user     = $customers[array_rand($customers)];
                $checkIn  = Carbon::today()->addDays($offset + $i)->toDateString();
                $checkOut = Carbon::parse($checkIn)->addDays($nights)->toDateString();

                $this->createBooking($hotel, $user, $room, $checkIn, $checkOut, $nights, $status);
                $count++;
            }
        }

        $this->command->info("TranquilooBookingSeeder: {$count} bookings created for \"{$hotel->name}\".");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function seedCustomers(): array
    {
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['guard_name' => 'web']);

        $names = [
            'Alice Mwangi', 'Brian Oduya', 'Carol Ndege', 'David Kamau',
            'Eve Mutua', 'Frank Otieno', 'Grace Wanjiru', 'Henry Kipchoge',
        ];

        $users = [];
        foreach ($names as $name) {
            $slug = Str::slug($name);
            $user = User::firstOrCreate(
                ['email' => "{$slug}@tranquiloo-demo.test"],
                ['name' => $name, 'password' => Hash::make('password123'), 'email_verified_at' => now()]
            );
            if (! $user->roles->contains('id', $customerRole->id)) {
                $user->roles()->attach($customerRole->id);
            }
            $users[] = $user;
        }

        $this->command->line('  <fg=green>✓</> ' . count($users) . ' demo customer accounts ready (password: password123).');

        return $users;
    }

    private function createBooking(Hotel $hotel, User $user, Room $room, string $checkIn, string $checkOut, int $nights, string $status): void
    {
        $rate      = (float) $room->roomType->base_price;
        $subtotal  = $rate * $nights;
        $taxTotal  = round($subtotal * self::TAX_RATE / 100, 2);
        $grand     = $subtotal + $taxTotal;
        $createdAt = Carbon::parse($checkIn)->subDays(rand(3, 20));

        $booking = Booking::create([
            'booking_number'               => Booking::generateBookingNumber(),
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
            'confirmed_at'                 => in_array($status, ['confirmed', 'checked_in', 'checked_out']) ? $createdAt->copy()->addHour() : null,
            'checked_in_at'                => in_array($status, ['checked_in', 'checked_out']) ? Carbon::parse($checkIn)->setHour(14) : null,
            'checked_out_at'               => $status === 'checked_out' ? Carbon::parse($checkOut)->setHour(11) : null,
            'cancelled_at'                 => in_array($status, ['cancelled', 'refunded']) ? $createdAt->copy()->addDays(rand(1, 3)) : null,
            'created_at'                   => $createdAt,
            'updated_at'                   => $createdAt,
        ]);

        BookingRoom::create([
            'booking_id'   => $booking->id,
            'room_id'      => $room->id,
            'room_type_id' => $room->room_type_id,
            'check_in'     => $checkIn,
            'check_out'    => $checkOut,
            'nightly_rate' => $rate,
            'nights'       => $nights,
            'sub_total'    => $subtotal,
        ]);

        // Block real availability for bookings that would still be holding the room today.
        if (in_array($status, ['pending', 'confirmed', 'checked_in'])) {
            $room->blockForBooking($booking);
        }

        $payMethods = ['airtel_money', 'mpesa', 'halotel', 'mix_by_yas'];
        $payMethod  = $payMethods[array_rand($payMethods)];
        $payStatus = match ($status) {
            'pending'   => 'pending',
            'cancelled' => 'failed',
            'refunded'  => 'refunded',
            default     => 'paid',
        };

        Payment::create([
            'booking_id'     => $booking->id,
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
            'pending'              => 'pending',
            'cancelled', 'refunded' => 'cancelled',
            default                => 'paid',
        };

        Invoice::create([
            'booking_id'     => $booking->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'subtotal'       => $subtotal,
            'tax_total'      => $taxTotal,
            'discount_total' => 0,
            'grand_total'    => $grand,
            'refund_amount'  => $status === 'refunded' ? $grand : 0,
            'currency'       => self::CURRENCY,
            'status'         => $invStatus,
            'issued_at'      => $createdAt,
            'due_at'         => $createdAt->copy()->addDay(),
            'paid_at'        => $invStatus === 'paid' ? $createdAt->copy()->addHours(2) : null,
            'created_at'     => $createdAt,
            'updated_at'     => $createdAt,
        ]);
    }
}
