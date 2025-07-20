<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Agency>
 */
class AgencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'renter_id' => null, // Set in seeder
            'name' => $this->faker->company(),
            'contact_email' => $this->faker->unique()->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'description' => $this->faker->catchPhrase(),
            'rating' => $this->faker->randomFloat(2, 3, 5),
            'total_reviews' => $this->faker->numberBetween(0, 100),
            'verification_status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'admin_feedback' => null,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
        ];
    }
}
