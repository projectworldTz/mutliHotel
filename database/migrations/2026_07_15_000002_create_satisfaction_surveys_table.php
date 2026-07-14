<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('satisfaction_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('token', 64)->unique();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comment')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();

            $table->timestamps();

            $table->index(['hotel_id', 'responded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('satisfaction_surveys');
    }
};
