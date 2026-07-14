<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->string('subject');
            $table->longText('body');
            $table->enum('audience', ['past_guests', 'upcoming_guests', 'all_guests'])->default('all_guests');
            $table->enum('status', ['draft', 'sent'])->default('draft');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->timestamp('sent_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['hotel_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};
