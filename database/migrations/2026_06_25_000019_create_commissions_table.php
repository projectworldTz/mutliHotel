<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            // null hotel_id = platform-wide default rate
            $table->foreignId('hotel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default('percentage'); // percentage|fixed
            $table->decimal('rate', 10, 2);
            $table->string('status')->default('active'); // active|inactive
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
