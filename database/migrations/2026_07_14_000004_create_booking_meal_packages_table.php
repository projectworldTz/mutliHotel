<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_meal_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_package_id')->nullable()->constrained('meal_packages')->nullOnDelete();

            // Snapshot fields — keep historical invoices correct even if the
            // package is later edited/deleted.
            $table->string('name');
            $table->string('pricing_type');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('sub_total', 12, 2);

            $table->timestamps();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_meal_packages');
    }
};
