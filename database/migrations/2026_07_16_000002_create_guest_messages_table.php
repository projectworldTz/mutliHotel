<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->enum('sender_type', ['guest', 'staff'])->default('guest');
            $table->text('message');
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['booking_id', 'created_at']);
            $table->index(['hotel_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_messages');
    }
};
