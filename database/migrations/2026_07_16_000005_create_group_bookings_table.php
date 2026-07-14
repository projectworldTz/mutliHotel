<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->string('event_name');
            $table->string('organizer_name');
            $table->string('organizer_email')->nullable();
            $table->string('organizer_phone')->nullable();

            $table->date('event_start');
            $table->date('event_end');
            $table->unsignedInteger('rooms_requested');
            $table->enum('status', ['inquiry', 'confirmed', 'completed', 'cancelled'])->default('inquiry');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['hotel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_bookings');
    }
};
