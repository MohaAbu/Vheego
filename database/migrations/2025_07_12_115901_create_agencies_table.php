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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renter_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->text('address');
            $table->text('description');
            $table->decimal('rating', 4, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);
            $table->enum('verification_status', ['pending', 'approved', 'rejected']);
            $table->text('admin_feedback')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
