<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['expense', 'payout'])->default('expense');
            $table->string('category')->nullable(); // utilities, supplies, salaries, maintenance, ...
            $table->string('payee')->nullable();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['hotel_id', 'expense_date']);
            $table->index(['hotel_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
