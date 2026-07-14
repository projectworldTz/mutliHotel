<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\HotelCategory;
use App\Models\Permission;
use App\Models\Role;
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
        Role::firstOrCreate(['name' => 'hotel-owner'],   ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'receptionist'], ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'manager'],      ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'cashier'],      ['guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'accountant'],   ['guard_name' => 'web']);
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
        $settings = [
            'site_name'              => 'StayBook Tanzania',
            'site_email'             => 'info@staybook.co.tz',
            'currency'               => 'TZS',
            'tax_rate'               => '0',
            'platform_commission'    => '10',
            'booking_expiry_minutes' => '60',
            'cancellation_policy'    => 'Free cancellation up to 24 hours before check-in. After that, the first night is non-refundable.',
            // Bank transfer details — seeded from .env so operators can configure without touching code
            'bank_account_name'      => env('BANK_ACCOUNT_NAME', 'Hotel Platform Ltd'),
            'bank_account_number'    => env('BANK_ACCOUNT_NUMBER', 'XXXX-XXXX-XXXX'),
            'bank_name'              => env('BANK_NAME', 'National Bank'),
            'bank_swift_code'        => env('BANK_SWIFT_CODE', 'XXXXXXXX'),
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->command->info('');
        $this->command->info('  Hotel platform seeded successfully!');
        $this->command->info('');
        $this->command->info('  Login credentials:');
        $this->command->info('  Admin  → admin@hotel.com / password');
        $this->command->info('  Guest  → guest@hotel.com / password');
    }
}
