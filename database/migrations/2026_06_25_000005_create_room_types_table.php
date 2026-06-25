<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->unsignedTinyInteger('max_guests')->default(2);
            $table->string('bed_type')->nullable(); // king, queen, twin, double, etc.
            $table->unsignedTinyInteger('beds_count')->default(1);
            $table->decimal('size_sqm', 6, 2)->nullable();
            $table->string('view_type')->nullable(); // sea, garden, city, pool, etc.
            $table->boolean('smoking')->default(false);
            $table->string('status')->default('active'); // active|inactive
            $table->timestamps();

            $table->unique(['hotel_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
