<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to use raw SQL to modify enum constraints
        if (DB::getDriverName() === 'pgsql') {
            // Drop the existing constraint
            DB::statement('ALTER TABLE reservations DROP CONSTRAINT IF EXISTS reservations_status_check');
            
            // Add the new constraint with all required status values
            DB::statement("ALTER TABLE reservations ADD CONSTRAINT reservations_status_check CHECK (status IN ('pending_payment', 'pending', 'active', 'confirmed', 'completed', 'cancelled'))");
        } else {
            // For MySQL and other databases, use Laravel's schema builder
            Schema::table('reservations', function (Blueprint $table) {
                $table->enum('status', ['pending_payment', 'pending', 'active', 'confirmed', 'completed', 'cancelled'])->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original constraint
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE reservations DROP CONSTRAINT IF EXISTS reservations_status_check');
            DB::statement("ALTER TABLE reservations ADD CONSTRAINT reservations_status_check CHECK (status IN ('active', 'completed', 'cancelled'))");
        } else {
            Schema::table('reservations', function (Blueprint $table) {
                $table->enum('status', ['active', 'completed', 'cancelled'])->change();
            });
        }
    }
};
