<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+'.rand(1,7).' days');
        return [
            'customer_id' => null, // Set in seeder
            'car_id' => null, // Set in seeder
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'total_price' => $this->faker->randomFloat(2, 100, 2000),
            'status' => $this->faker->randomElement(['active', 'completed', 'cancelled']),
        ];
    }
}
