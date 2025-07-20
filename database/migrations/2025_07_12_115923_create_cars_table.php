<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->string('make');
            $table->string('model');
            $table->integer('year');
            $table->enum('category', ['economy', 'luxury', 'suv', 'sedan', 'hatchback', 'convertible']);
            $table->string('license_plate')->unique();
            $table->decimal('rental_price_per_day', 10, 2);
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid']);
            $table->enum('transmission', ['manual', 'automatic']);
            $table->integer('seats');
            $table->json('features');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('delisted_reason')->nullable();
            $table->foreignId('delisted_by')->nullable()->constrained('users');
            $table->timestamp('delisted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
