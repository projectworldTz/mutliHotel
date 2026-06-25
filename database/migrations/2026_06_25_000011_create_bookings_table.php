<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('pending');
            // pending|confirmed|checked_in|checked_out|cancelled|refunded|no_show
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('nights');
            $table->unsignedTinyInteger('guests_adults')->default(1);
            $table->unsignedTinyInteger('guests_children')->default(0);
            $table->decimal('sub_total', 12, 2);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->decimal('grand_total', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('special_requests')->nullable();
            $table->text('notes')->nullable();
            $table->text('cancellation_policy_snapshot')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['hotel_id', 'check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
