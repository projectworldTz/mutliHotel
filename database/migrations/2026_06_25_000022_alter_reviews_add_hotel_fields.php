<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop FK to products (transitioning to hotels)
            $table->dropForeign(['product_id']);
            $table->unsignedBigInteger('product_id')->nullable()->change();

            // Add hotel booking fields
            $table->foreignId('hotel_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->after('hotel_id')->constrained()->nullOnDelete();
            $table->string('title')->nullable()->after('rating');
            $table->text('response')->nullable()->after('comment');
            $table->timestamp('responded_at')->nullable()->after('response');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['hotel_id', 'booking_id', 'title', 'response', 'responded_at']);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
