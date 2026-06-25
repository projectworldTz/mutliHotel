<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // null = platform-wide coupon, set = hotel-specific coupon
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('room_type_id')->nullable()->after('hotel_id')->constrained()->nullOnDelete();
            $table->decimal('min_booking_amount', 12, 2)->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropForeign(['room_type_id']);
            $table->dropColumn(['hotel_id', 'room_type_id', 'min_booking_amount']);
        });
    }
};
