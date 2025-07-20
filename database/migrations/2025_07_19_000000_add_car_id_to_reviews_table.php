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
        Schema::table('reviews', function (Blueprint $table) {
            // Add car_id column for direct car reviews
            $table->foreignId('car_id')->nullable()->after('reservation_id')->constrained('cars')->onDelete('cascade');
            
            // Make reservation_id nullable since we now support direct car reviews
            $table->foreignId('reservation_id')->nullable()->change();
            
            // Remove the unique constraint on reservation_id since it's now nullable
            $table->dropUnique(['reservation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Drop car_id column
            $table->dropForeign(['car_id']);
            $table->dropColumn('car_id');
            
            // Make reservation_id required again
            $table->foreignId('reservation_id')->nullable(false)->change();
            
            // Add back the unique constraint
            $table->unique('reservation_id');
        });
    }
};