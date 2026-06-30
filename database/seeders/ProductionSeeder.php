<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\HotelCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Run once on a fresh production deployment.
 *
 * Before running, add these to your .env:
 *
 *   ADMIN_NAME="Platform Admin"
 *   ADMIN_EMAIL=admin@yourdomain.com
 *   ADMIN_PASSWORD=ChangeMe@Prod1
 *
 *   OWNER_NAME="Your Hotel Name"
 *   OWNER_EMAIL=owner@yourdomain.com
 *   OWNER_PASSWORD=ChangeMe@Prod2
 *
 * Then run:
 *   php artisan db:seed --class=ProductionSeeder
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Roles ──────────────────────────────────────────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin'],   ['guard_name' => 'web']);
        $hotelOwner = Role::firstOrCreate(['name' => 'hotel-owner'],   ['guard_name' => 'web']);
                      Role::firstOrCreate(['name' => 'receptionist'], ['guard_name' => 'web']);
                      Role::firstOrCreate(['name' => 'manager'],      ['guard_name' => 'web']);
                      Role::firstOrCreate(['name' => 'cashier'],      ['guard_name' => 'web']);
                      Role::firstOrCreate(['name' => 'customer'],     ['guard_name' => 'web']);

        $this->command->line('  <fg=green>✓</> Roles ready.');

        // ── 2. Permissions ────────────────────────────────────────────────────
        foreach ([
            'manage-hotels', 'manage-bookings', 'manage-users',
            'manage-settings', 'view-reports',
            'manage-own-hotel', 'manage-own-bookings',
        ] as $perm) {
            Permission::firstOrCreate(['name' => $perm], ['guard_name' => 'web']);
        }

        $this->command->line('  <fg=green>✓</> Permissions ready.');

        // ── 3. Super Admin ────────────────────────────────────────────────────
        $adminEmail    = env('ADMIN_EMAIL', 'admin@yourdomain.com');
        $adminPassword = env('ADMIN_PASSWORD');

        if (! $adminPassword) {
            $this->command->error('ADMIN_PASSWORD is not set in .env — aborting.');
            return;
        }

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => env('ADMIN_NAME', 'Platform Admin'),
                'password'          => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        $admin->roles()->syncWithoutDetaching([$superAdmin->id]);

        $this->command->line("  <fg=green>✓</> Super admin → {$adminEmail}");

        // ── 4. Hotel Owner ────────────────────────────────────────────────────
        $ownerEmail    = env('OWNER_EMAIL', 'owner@yourdomain.com');
        $ownerPassword = env('OWNER_PASSWORD');

        if (! $ownerPassword) {
            $this->command->error('OWNER_PASSWORD is not set in .env — aborting.');
            return;
        }

        $owner = User::firstOrCreate(
            ['email' => $ownerEmail],
            [
                'name'              => env('OWNER_NAME', 'Hotel Owner'),
                'password'          => Hash::make($ownerPassword),
                'email_verified_at' => now(),
            ]
        );

        $owner->roles()->syncWithoutDetaching([$hotelOwner->id]);

        $this->command->line("  <fg=green>✓</> Hotel owner → {$ownerEmail}");

        // ── 5. Hotel Categories ───────────────────────────────────────────────
        foreach ([
            ['name' => 'Luxury Hotel',   'sort_order' => 1],
            ['name' => 'Budget Hotel',   'sort_order' => 2],
            ['name' => 'Beach Resort',   'sort_order' => 3],
            ['name' => 'Business Hotel', 'sort_order' => 4],
            ['name' => 'Boutique Hotel', 'sort_order' => 5],
            ['name' => 'Safari Lodge',   'sort_order' => 6],
            ['name' => 'Mountain Lodge', 'sort_order' => 7],
        ] as $cat) {
            HotelCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'sort_order' => $cat['sort_order'], 'active' => true]
            );
        }

        $this->command->line('  <fg=green>✓</> Hotel categories ready.');

        // ── 6. Amenities ──────────────────────────────────────────────────────
        foreach ([
            ['name' => 'Free WiFi',          'icon' => 'wifi',         'category' => 'connectivity'],
            ['name' => 'Business Centre',    'icon' => 'briefcase',    'category' => 'connectivity'],
            ['name' => 'Swimming Pool',      'icon' => 'waves',        'category' => 'recreation'],
            ['name' => 'Fitness Centre',     'icon' => 'dumbbell',     'category' => 'recreation'],
            ['name' => 'Spa & Wellness',     'icon' => 'sparkles',     'category' => 'recreation'],
            ['name' => 'Tennis Court',       'icon' => 'trophy',       'category' => 'recreation'],
            ['name' => 'Restaurant',         'icon' => 'utensils',     'category' => 'dining'],
            ['name' => 'Bar & Lounge',       'icon' => 'glass-water',  'category' => 'dining'],
            ['name' => 'Room Service',       'icon' => 'bell',         'category' => 'dining'],
            ['name' => 'Breakfast Included', 'icon' => 'coffee',       'category' => 'dining'],
            ['name' => 'Free Parking',       'icon' => 'car',          'category' => 'transport'],
            ['name' => 'Airport Shuttle',    'icon' => 'plane',        'category' => 'transport'],
            ['name' => 'Air Conditioning',   'icon' => 'wind',         'category' => 'services'],
            ['name' => '24/7 Front Desk',    'icon' => 'clock',        'category' => 'services'],
            ['name' => 'Laundry Service',    'icon' => 'shirt',        'category' => 'services'],
            ['name' => 'Conference Room',    'icon' => 'presentation', 'category' => 'services'],
            ['name' => 'Children Play Area', 'icon' => 'face-smile',   'category' => 'services'],
            ['name' => 'Pet Friendly',       'icon' => 'heart',        'category' => 'services'],
        ] as $amenity) {
            Amenity::firstOrCreate(['name' => $amenity['name']], [
                'icon'     => $amenity['icon'],
                'category' => $amenity['category'],
            ]);
        }

        $this->command->line('  <fg=green>✓</> Amenities ready.');

        // ── 7. Platform Settings ──────────────────────────────────────────────
        $defaults = [
            'site_name'              => env('APP_NAME', 'Hotel Booking Platform'),
            'site_email'             => env('MAIL_FROM_ADDRESS', 'info@yourdomain.com'),
            'currency'               => env('DEFAULT_CURRENCY', 'TZS'),
            'tax_rate'               => env('DEFAULT_TAX_RATE', '18'),
            'platform_commission'    => env('PLATFORM_COMMISSION', '10'),
            'booking_expiry_minutes' => '60',
            'cancellation_policy'    => 'Free cancellation up to 48 hours before check-in. Partial refund within 24-48 hours. No refund under 24 hours.',
            'bank_account_name'      => env('BANK_ACCOUNT_NAME', ''),
            'bank_account_number'    => env('BANK_ACCOUNT_NUMBER', ''),
            'bank_name'              => env('BANK_NAME', ''),
            'bank_swift_code'        => env('BANK_SWIFT_CODE', ''),
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->command->line('  <fg=green>✓</> Platform settings initialised.');

        // ── Summary ───────────────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('  Production seed complete.');
        $this->command->newLine();
        $this->command->line("  Super Admin  →  {$adminEmail}");
        $this->command->line("  Hotel Owner  →  {$ownerEmail}");
        $this->command->newLine();
        $this->command->warn('  Remember to remove ADMIN_PASSWORD / OWNER_PASSWORD from .env after first login!');
    }
}
