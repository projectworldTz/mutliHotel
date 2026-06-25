<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop FK to orders (transitioning to bookings)
            $table->dropForeign(['order_id']);
            $table->unsignedBigInteger('order_id')->nullable()->change();

            // Add FK to bookings
            $table->foreignId('booking_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('currency', 3)->default('USD')->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['booking_id', 'currency']);
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
        });
    }
};
