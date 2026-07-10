<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            // MySQL's unique index counts soft-deleted rows, so a deleted room
            // permanently blocks re-using its room_number. Enforced in the
            // validation layer instead (HotelController::storeRoom/updateRoom),
            // which correctly excludes soft-deleted rooms.
            $table->dropUnique(['hotel_id', 'room_number']);
            $table->index(['hotel_id', 'room_number']);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['hotel_id', 'room_number']);
            $table->unique(['hotel_id', 'room_number']);
        });
    }
};
