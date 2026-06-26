<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Drop order matters — child tables first to respect foreign keys
    private array $tables = [
        'returns',
        'inventories',
        'cart_items',
        'carts',
        'wishlists',
        'order_items',
        'orders',
        'product_images',
        'product_variants',
        'products',
        'brands',
        'categories',
    ];

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Legacy e-commerce tables are not restored — re-run the original migrations if needed
    }
};
