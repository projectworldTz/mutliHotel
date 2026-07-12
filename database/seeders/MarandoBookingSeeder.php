<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MarandoBookingSeeder extends Seeder
{
    private const HOTEL_SLUG = 'luxury-hotel';
    private const TAX_RATE   = 0;   // %
    private const CURRENCY   = 'TZS';

    private int $bookingSeq = 1;

    // room_id => [room_type_id, nightly_rate], filled in during run()
    private array $rooms = [];

    public function run(): void
    {
        \Illuminate\Database\Eloquent\Model::unguard();

        $hotel = Hotel::where('slug', self::HOTEL_SLUG)->firstOrFail();

        $this->rooms = $this->seedRoomsAndTypes($hotel);
        $this->seedHotelImages($hotel);
        $this->seedRoomTypeImages($hotel);
        $customers   = $this->seedCustomers();
        $roomIds     = array_keys($this->rooms);

        // Status distribution: 60 total (same shape as BookingSeeder)
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
                $checkIn  = Carbon::today()->addDays($offset + ($i * 1))->toDateString();
                $checkOut = Carbon::parse($checkIn)->addDays($nights)->toDateString();

                $this->createBooking($hotel, $user, $roomId, $checkIn, $checkOut, $nights, $status);
                $count++;
            }
        }

        \Illuminate\Database\Eloquent\Model::reguard();

        $this->command->info("MarandoBookingSeeder: {$count} bookings created for \"{$hotel->name}\".");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function seedRoomsAndTypes(Hotel $hotel): array
    {
        $typeSpecs = [
            ['name' => 'Standard Room', 'price' => 90000,  'guests' => 2, 'bed' => 'Queen', 'beds' => 1, 'count' => 10],
            ['name' => 'Deluxe Room',   'price' => 150000, 'guests' => 2, 'bed' => 'King',  'beds' => 1, 'count' => 6],
            ['name' => 'Executive Suite', 'price' => 260000, 'guests' => 3, 'bed' => 'King', 'beds' => 1, 'count' => 5],
            ['name' => 'Family Room',   'price' => 200000, 'guests' => 4, 'bed' => 'Twin',  'beds' => 2, 'count' => 5],
        ];

        $rooms = [];
        $floor = 1;

        foreach ($typeSpecs as $spec) {
            $roomType = RoomType::firstOrCreate(
                ['hotel_id' => $hotel->id, 'slug' => Str::slug($spec['name'])],
                [
                    'name'        => $spec['name'],
                    'description' => "Comfortable {$spec['name']} with modern amenities.",
                    'base_price'  => $spec['price'],
                    'max_guests'  => $spec['guests'],
                    'bed_type'    => $spec['bed'],
                    'beds_count'  => $spec['beds'],
                    'status'      => 'active',
                ]
            );

            for ($n = 1; $n <= $spec['count']; $n++) {
                $roomNumber = "{$floor}0" . $n;
                $room = Room::firstOrCreate(
                    ['hotel_id' => $hotel->id, 'room_number' => $roomNumber],
                    ['room_type_id' => $roomType->id, 'floor' => $floor, 'status' => 'available']
                );
                $rooms[$room->id] = [$roomType->id, (float) $spec['price']];
            }

            $floor++;
        }

        $this->command->line('  <fg=green>✓</> ' . count($rooms) . ' rooms across ' . count($typeSpecs) . ' room types ready for ' . $hotel->name . '.');

        return $rooms;
    }

    private function seedHotelImages(Hotel $hotel): void
    {
        if ($hotel->images()->exists()) {
            $this->command->line('  <fg=yellow>–</> Hotel gallery images already exist, skipping.');
            return;
        }

        $u  = 'https://images.unsplash.com/photo-';
        $qs = '?w=1200&h=800&fit=crop&q=80';

        $images = [
            ['url' => "{$u}1618773928121-c32242e63f39{$qs}", 'caption' => 'Hotel exterior at dusk', 'cover' => true],
            ['url' => "{$u}1566073771259-6a8506099945{$qs}", 'caption' => 'Grand lobby'],
            ['url' => "{$u}1571003123894-1f0594d16a2b{$qs}", 'caption' => 'Swimming pool'],
            ['url' => "{$u}1517248135467-4c7edcad34c4{$qs}", 'caption' => 'Restaurant & dining'],
            ['url' => "{$u}1520250497591-112f2f40a3f4{$qs}", 'caption' => 'Lounge area'],
            ['url' => "{$u}1445019980597-93fa8acb246c{$qs}", 'caption' => 'Guest bathroom'],
        ];

        foreach ($images as $i => $img) {
            HotelImage::create([
                'hotel_id'    => $hotel->id,
                'path'        => 'unsplash/' . Str::afterLast($img['url'], 'photo-'),
                'url'         => $img['url'],
                'caption'     => $img['caption'],
                'sort_order'  => $i,
                'is_featured' => $img['cover'] ?? false,
            ]);
        }

        $this->command->line('  <fg=green>✓</> ' . count($images) . ' hotel gallery images ready.');
    }

    private function seedRoomTypeImages(Hotel $hotel): void
    {
        $u  = 'https://images.unsplash.com/photo-';
        $qs = '?w=1200&h=800&fit=crop&q=80';

        $images = [
            'standard-room' => [
                ['url' => "{$u}1595576508898-0ad5c879a061{$qs}", 'caption' => 'Comfortable queen bed', 'cover' => true],
                ['url' => "{$u}1587985064135-0366536eab42{$qs}", 'caption' => 'Room seating area'],
                ['url' => "{$u}1566665200767-cddfc66b48be{$qs}", 'caption' => 'Workspace and wardrobe'],
                ['url' => "{$u}1445019980597-93fa8acb246c{$qs}", 'caption' => 'En-suite bathroom'],
            ],
            'deluxe-room' => [
                ['url' => "{$u}1611892440504-42a792e24d32{$qs}", 'caption' => 'King bed with city views', 'cover' => true],
                ['url' => "{$u}1582719478250-c89cae4dc85b{$qs}", 'caption' => 'Elegant room décor'],
                ['url' => "{$u}1590490360182-c33d57733427{$qs}", 'caption' => 'Modern en-suite bathroom'],
                ['url' => "{$u}1629140727571-9b5ae6ef3f52{$qs}", 'caption' => 'Floor-to-ceiling windows'],
            ],
            'executive-suite' => [
                ['url' => "{$u}1578683010236-d716f9a3f461{$qs}", 'caption' => 'Lounge and sleeping area', 'cover' => true],
                ['url' => "{$u}1560185893-a55cbc8c57e8{$qs}", 'caption' => 'King bed suite'],
                ['url' => "{$u}1552321554-5fefe8c9ef14{$qs}", 'caption' => 'Luxury soaking tub'],
                ['url' => "{$u}1541123437800-1bb1317badc2{$qs}", 'caption' => 'Private balcony'],
            ],
            'family-room' => [
                ['url' => "{$u}1560185127-6ac024de38cf{$qs}", 'caption' => 'Twin bed configuration', 'cover' => true],
                ['url' => "{$u}1571896349842-33c89424de2d{$qs}", 'caption' => 'Spacious bathroom'],
                ['url' => "{$u}1551882547-ff40c599fb04{$qs}", 'caption' => 'City view from the room'],
                ['url' => "{$u}1631049307264-da0ec9d70304{$qs}", 'caption' => 'Extra bed for children'],
            ],
        ];

        $types = RoomType::where('hotel_id', $hotel->id)->get()->keyBy('slug');

        foreach ($images as $slug => $imgs) {
            $rt = $types[$slug] ?? null;
            if (! $rt || $rt->images()->exists()) {
                continue;
            }

            foreach ($imgs as $i => $img) {
                RoomImage::create([
                    'room_type_id' => $rt->id,
                    'path'         => 'unsplash/' . Str::afterLast($img['url'], 'photo-'),
                    'url'          => $img['url'],
                    'caption'      => $img['caption'],
                    'sort_order'   => $i,
                    'is_featured'  => $img['cover'] ?? false,
                ]);
            }

            $this->command->line("  <fg=green>✓</> {$rt->name}: " . count($imgs) . ' images');
        }
    }

    private function seedCustomers(): array
    {
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $names = [
            'Alice Mwangi', 'Brian Oduya', 'Carol Ndege', 'David Kamau',
            'Eve Mutua',    'Frank Otieno','Grace Wanjiru','Henry Kipchoge',
            'Irene Achieng','James Maina', 'Karen Njeri',  'Leo Omondi',
            'Mary Wambui',  'Nick Osei',   'Olivia Hamisi',
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

        return $users;
    }

    private function createBooking(Hotel $hotel, User $user, int $roomId, string $checkIn, string $checkOut, int $nights, string $status): void
    {
        [$roomTypeId, $rate] = $this->rooms[$roomId];

        $subtotal  = $rate * $nights;
        $taxTotal  = round($subtotal * self::TAX_RATE / 100);
        $grand     = $subtotal + $taxTotal;
        $createdAt = Carbon::parse($checkIn)->subDays(rand(3, 30));

        $booking = Booking::create([
            'booking_number'               => 'BK-MRD-' . str_pad($this->bookingSeq++, 5, '0', STR_PAD_LEFT),
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
            'confirmed_at'                 => in_array($status, ['confirmed','checked_in','checked_out']) ? $createdAt->copy()->addHours(1) : null,
            'checked_in_at'                => in_array($status, ['checked_in','checked_out']) ? Carbon::parse($checkIn)->setHour(14) : null,
            'checked_out_at'               => $status === 'checked_out' ? Carbon::parse($checkOut)->setHour(11) : null,
            'cancelled_at'                 => in_array($status, ['cancelled','refunded']) ? $createdAt->copy()->addDays(rand(1, 3)) : null,
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

        $payMethod = ['airtel_money','mpesa','halotel','mix_by_yas'][rand(0,3)];
        $payStatus = match($status) {
            'pending'    => 'pending',
            'cancelled'  => 'failed',
            'refunded'   => 'refunded',
            default      => 'confirmed',
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

        $invStatus = match($status) {
            'pending'              => 'pending',
            'cancelled','refunded' => 'cancelled',
            default                => 'paid',
        };
        Invoice::create([
            'booking_id'      => $booking->id,
            'invoice_number'  => 'INV-' . $booking->booking_number,
            'subtotal'        => $subtotal,
            'tax_total'       => $taxTotal,
            'discount_total'  => 0,
            'grand_total'     => $grand,
            'refund_amount'   => $status === 'refunded' ? $grand : 0,
            'currency'        => self::CURRENCY,
            'status'          => $invStatus,
            'issued_at'       => $createdAt,
            'due_at'          => $createdAt->copy()->addDays(1),
            'paid_at'         => $invStatus === 'paid' ? $createdAt->copy()->addHours(2) : null,
            'created_at'      => $createdAt,
            'updated_at'      => $createdAt,
        ]);
    }
}
