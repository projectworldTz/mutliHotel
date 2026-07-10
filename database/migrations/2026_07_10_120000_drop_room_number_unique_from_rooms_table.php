<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL's unique index counts soft-deleted rows, so a deleted room
        // permanently blocks re-using its room_number. Enforced in the
        // validation layer instead (HotelController::storeRoom/updateRoom),
        // which correctly excludes soft-deleted rooms.
        //
        // The unique index is also the only index covering the hotel_id
        // foreign key, so the replacement index must be added first —
        // MySQL refuses to drop an index a foreign key still depends on.
        Schema::table('rooms', function (Blueprint $table) {
            $table->index(['hotel_id', 'room_number']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique(['hotel_id', 'room_number']);
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->unique(['hotel_id', 'room_number']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['hotel_id', 'room_number']);
        });
    }
};
