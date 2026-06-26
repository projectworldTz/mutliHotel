<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Hotel;
use App\Models\HotelCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ─────────────────────────────────────────────────────────────
        $superAdmin   = Role::firstOrCreate(['name' => 'super-admin'],   ['guard_name' => 'web']);
        $hotelOwner   = Role::firstOrCreate(['name' => 'hotel-owner'],   ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'receptionist'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'customer'],     ['guard_name' => 'web']);

        // ── Permissions ───────────────────────────────────────────────────────
        foreach ([
            'manage-hotels', 'manage-bookings', 'manage-users',
            'manage-settings', 'view-reports',
            'manage-own-hotel', 'manage-own-bookings',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm], ['guard_name' => 'web']);
        }

        // ── Users ─────────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@hotel.com'],
            ['name' => 'Platform Admin', 'password' => bcrypt('password'), 'phone' => '+255700000001']
        );
        $admin->roles()->syncWithoutDetaching([$superAdmin->id]);

        $owner = User::firstOrCreate(
            ['email' => 'owner@hotel.com'],
            ['name' => 'Hotel Owner', 'password' => bcrypt('password'), 'phone' => '+255700000002']
        );
        $owner->roles()->syncWithoutDetaching([$hotelOwner->id]);

        User::firstOrCreate(
            ['email' => 'guest@hotel.com'],
            ['name' => 'Test Guest', 'password' => bcrypt('password'), 'phone' => '+255700000003']
        );

        // ── Hotel Categories ──────────────────────────────────────────────────
        $categoryData = [
            ['name' => 'Luxury Hotel',   'sort_order' => 1],
            ['name' => 'Budget Hotel',   'sort_order' => 2],
            ['name' => 'Beach Resort',   'sort_order' => 3],
            ['name' => 'Business Hotel', 'sort_order' => 4],
            ['name' => 'Boutique Hotel', 'sort_order' => 5],
            ['name' => 'Safari Lodge',   'sort_order' => 6],
            ['name' => 'Mountain Lodge', 'sort_order' => 7],
        ];
        foreach ($categoryData as $cat) {
            HotelCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'sort_order' => $cat['sort_order'], 'active' => true]
            );
        }

        // ── Amenities ─────────────────────────────────────────────────────────
        foreach ([
            ['name' => 'Free WiFi',           'icon' => 'wifi',         'category' => 'connectivity'],
            ['name' => 'Business Centre',     'icon' => 'briefcase',    'category' => 'connectivity'],
            ['name' => 'Swimming Pool',       'icon' => 'waves',        'category' => 'recreation'],
            ['name' => 'Fitness Centre',      'icon' => 'dumbbell',     'category' => 'recreation'],
            ['name' => 'Spa & Wellness',      'icon' => 'sparkles',     'category' => 'recreation'],
            ['name' => 'Tennis Court',        'icon' => 'trophy',       'category' => 'recreation'],
            ['name' => 'Restaurant',          'icon' => 'utensils',     'category' => 'dining'],
            ['name' => 'Bar & Lounge',        'icon' => 'glass-water',  'category' => 'dining'],
            ['name' => 'Room Service',        'icon' => 'bell',         'category' => 'dining'],
            ['name' => 'Breakfast Included',  'icon' => 'coffee',       'category' => 'dining'],
            ['name' => 'Free Parking',        'icon' => 'car',          'category' => 'transport'],
            ['name' => 'Airport Shuttle',     'icon' => 'plane',        'category' => 'transport'],
            ['name' => 'Air Conditioning',    'icon' => 'wind',         'category' => 'services'],
            ['name' => '24/7 Front Desk',     'icon' => 'clock',        'category' => 'services'],
            ['name' => 'Laundry Service',     'icon' => 'shirt',        'category' => 'services'],
            ['name' => 'Conference Room',     'icon' => 'presentation', 'category' => 'services'],
            ['name' => 'Children Play Area',  'icon' => 'face-smile',   'category' => 'services'],
            ['name' => 'Pet Friendly',        'icon' => 'heart',        'category' => 'services'],
        ] as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity['name']], [
                'icon'     => $amenity['icon'],
                'category' => $amenity['category'],
            ]);
        }

        // ── Platform Settings ─────────────────────────────────────────────────
        foreach ([
            'site_name'              => 'StayBook Tanzania',
            'site_email'             => 'info@staybook.co.tz',
            'currency'               => 'TZS',
            'tax_rate'               => '18',
            'platform_commission'    => '10',
            'booking_expiry_minutes' => '60',
            'cancellation_policy'    => 'Free cancellation up to 24 hours before check-in. After that, the first night is non-refundable.',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // ── Sample Hotel (for testing) ────────────────────────────────────────
        $category = HotelCategory::where('slug', 'luxury-hotel')->first();

        $hotel = Hotel::firstOrCreate(
            ['slug' => 'kilimanjaro-grand-hotel'],
            [
                'owner_id'            => $owner->id,
                'hotel_category_id'   => $category?->id,
                'name'                => 'Kilimanjaro Grand Hotel',
                'description'         => "Experience luxury at the foot of Africa's highest peak. Our 5-star hotel offers breathtaking views of Mount Kilimanjaro with world-class amenities and warm Tanzanian hospitality.",
                'star_rating'         => 5,
                'status'              => 'active',
                'featured'            => true,
                'phone'               => '+255272754551',
                'email'               => 'info@kilimanjarogrand.co.tz',
                'address'             => 'Kilimanjaro Road',
                'city'                => 'Moshi',
                'state'               => 'Kilimanjaro',
                'country'             => 'Tanzania',
                'postal_code'         => '25100',
                'latitude'            => -3.3731,
                'longitude'           => 37.3408,
                'check_in_time'       => '14:00',
                'check_out_time'      => '11:00',
                'cancellation_policy' => 'Free cancellation 24 hours before check-in.',
            ]
        );

        // Sync amenities
        $amenityIds = Amenity::whereIn('name', [
            'Free WiFi', 'Swimming Pool', 'Restaurant',
            'Air Conditioning', 'Free Parking', '24/7 Front Desk',
        ])->pluck('id');
        $hotel->amenities()->syncWithoutDetaching($amenityIds);

        // Room types + physical rooms
        $roomTypes = [
            [
                'slug'        => 'standard-room',
                'name'        => 'Standard Room',
                'description' => 'Comfortable room with a king-size bed, en-suite bathroom, and garden view.',
                'base_price'  => 120000,
                'max_guests'  => 2,
                'bed_type'    => 'King',
                'beds_count'  => 1,
                'size_sqm'    => 28,
                'view_type'   => 'Garden View',
                'quantity'    => 10,
                'floor_start' => 1,
                'room_start'  => 101,
            ],
            [
                'slug'        => 'deluxe-suite',
                'name'        => 'Deluxe Suite',
                'description' => 'Spacious suite with stunning Kilimanjaro views, living area, and premium amenities.',
                'base_price'  => 250000,
                'max_guests'  => 3,
                'bed_type'    => 'King',
                'beds_count'  => 1,
                'size_sqm'    => 55,
                'view_type'   => 'Mountain View',
                'quantity'    => 5,
                'floor_start' => 2,
                'room_start'  => 201,
            ],
            [
                'slug'        => 'family-room',
                'name'        => 'Family Room',
                'description' => 'Large room with two queen beds, perfect for families with children.',
                'base_price'  => 180000,
                'max_guests'  => 4,
                'bed_type'    => 'Queen',
                'beds_count'  => 2,
                'size_sqm'    => 42,
                'view_type'   => 'Pool View',
                'quantity'    => 6,
                'floor_start' => 3,
                'room_start'  => 301,
            ],
        ];

        foreach ($roomTypes as $rtData) {
            $roomType = RoomType::firstOrCreate(
                ['hotel_id' => $hotel->id, 'slug' => $rtData['slug']],
                [
                    'name'        => $rtData['name'],
                    'description' => $rtData['description'],
                    'base_price'  => $rtData['base_price'],
                    'max_guests'  => $rtData['max_guests'],
                    'bed_type'    => $rtData['bed_type'],
                    'beds_count'  => $rtData['beds_count'],
                    'size_sqm'    => $rtData['size_sqm'],
                    'view_type'   => $rtData['view_type'],
                    'smoking'     => false,
                ]
            );

            for ($i = 0; $i < $rtData['quantity']; $i++) {
                Room::firstOrCreate(
                    ['hotel_id' => $hotel->id, 'room_number' => (string)($rtData['room_start'] + $i)],
                    [
                        'room_type_id' => $roomType->id,
                        'floor'        => $rtData['floor_start'],
                        'status'       => 'available',
                    ]
                );
            }
        }

        $this->command->info('');
        $this->command->info('  Hotel platform seeded successfully!');
        $this->command->info('');
        $this->command->info('  Login credentials:');
        $this->command->info('  Admin  → admin@hotel.com / password');
        $this->command->info('  Owner  → owner@hotel.com / password');
        $this->command->info('  Guest  → guest@hotel.com / password');
        $this->command->info('');
        $this->command->info('  Sample hotel: Kilimanjaro Grand Hotel (21 rooms, 3 room types)');
    }
}
