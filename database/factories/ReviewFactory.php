<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $comments = [
            // Positive
            'Amazing experience! Highly recommended.',
            'The car was spotless and ran perfectly.',
            'Great service and friendly staff.',
            'Would definitely rent again.',
            'Excellent value for money.',
            // Neutral
            'The car was okay, nothing special.',
            'Average experience, but got the job done.',
            'Pickup and drop-off were smooth.',
            'Decent car for the price.',
            // Negative
            'Car was not very clean.',
            'Had some issues with the air conditioning.',
            'The car was older than expected.',
            'Customer service could be improved.',
            'Not the best experience, but acceptable.'
        ];
        return [
            'reservation_id' => null, // Set in seeder
            'customer_id' => null, // Set in seeder
            'agency_id' => null, // Set in seeder
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional(0.2)->randomElement($comments), // 20% chance of null
        ];
    }
}
