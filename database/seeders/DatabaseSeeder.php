<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Page;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::create(['name' => 'Super Admin']);
        $managerRole = Role::create(['name' => 'Manager']);

        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $user->roles()->attach($adminRole);

        Category::create(['name' => 'Living Room', 'slug' => 'living-room']);
        Category::create(['name' => 'Bedroom', 'slug' => 'bedroom']);
        Category::create(['name' => 'Dining', 'slug' => 'dining']);

        Brand::create(['name' => 'Nordic Home']);
        Brand::create(['name' => 'Urban Loft']);

        Product::create([
            'name' => 'Modern Sofa',
            'slug' => 'modern-sofa',
            'description' => 'A comfortable sofa with premium fabric and modern styling.',
            'price' => 89900,
            'stock' => 25,
            'category_id' => 1,
            'brand_id' => 1,
            'status' => 'published',
        ]);

        Product::create([
            'name' => 'Oak Dining Table',
            'slug' => 'oak-dining-table',
            'description' => 'Solid oak dining table for family dinners and entertaining.',
            'price' => 129900,
            'stock' => 10,
            'category_id' => 3,
            'brand_id' => 2,
            'status' => 'published',
        ]);

        Coupon::create(['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'expires_at' => now()->addMonths(2)]);

        Setting::create(['key' => 'store_name', 'value' => 'FurniCraft']);
        Setting::create(['key' => 'store_email', 'value' => 'hello@furnicraft.example']);

        Page::create(['title' => 'About Us', 'slug' => 'about-us', 'content' => 'Welcome to FurniCraft, the home of premium furniture.']);

        Testimonial::create(['name' => 'Ava Martin', 'position' => 'Interior Designer', 'content' => 'FurniCraft made our showroom look incredible. The quality and support are exceptional.']);
    }
}
