<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('pricing_type', ['per_night', 'per_stay', 'per_guest'])->default('per_stay');
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->index(['hotel_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_packages');
    }
};
