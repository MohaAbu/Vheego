<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $carData = [
            // Realistic makes and models
            ['make' => 'Toyota', 'models' => ['Corolla', 'Camry', 'RAV4', 'Prius', 'Yaris']],
            ['make' => 'Honda', 'models' => ['Civic', 'Accord', 'CR-V', 'Fit', 'Pilot']],
            ['make' => 'Ford', 'models' => ['Focus', 'Fusion', 'Escape', 'Mustang', 'Explorer']],
            ['make' => 'BMW', 'models' => ['3 Series', '5 Series', 'X3', 'X5', 'Z4']],
            ['make' => 'Mercedes-Benz', 'models' => ['C-Class', 'E-Class', 'GLA', 'GLC', 'S-Class']],
            ['make' => 'Hyundai', 'models' => ['Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Accent']],
            ['make' => 'Kia', 'models' => ['Rio', 'Optima', 'Sportage', 'Sorento', 'Soul']],
            ['make' => 'Chevrolet', 'models' => ['Malibu', 'Cruze', 'Equinox', 'Impala', 'Spark']],
            ['make' => 'Audi', 'models' => ['A3', 'A4', 'A6', 'Q5', 'Q7']],
            ['make' => 'Volkswagen', 'models' => ['Golf', 'Passat', 'Tiguan', 'Jetta', 'Polo']],
        ];
        $car = fake()->randomElement($carData);
        $make = $car['make'];
        $model = fake()->randomElement($car['models']);
        $categories = ['economy', 'luxury', 'suv', 'sedan', 'hatchback', 'convertible'];
        $fuel_types = ['gasoline', 'diesel', 'electric', 'hybrid'];
        $transmissions = ['manual', 'automatic'];
        $allFeatures = ['air conditioning', 'gps', 'bluetooth', 'heated seats', 'backup camera', 'cruise control', 'sunroof', 'leather seats', 'parking sensors', 'keyless entry'];
        $features = fake()->randomElements($allFeatures, rand(3, 6));
        $isActive = fake()->boolean(90); // 90% active
        $delistedReason = $isActive ? null : fake()->randomElement(['Maintenance', 'Delisted by admin', 'Expired registration']);
        $delistedBy = $isActive ? null : fake()->numberBetween(1, 5); 
        $delistedAt = $isActive ? null : now();
        return [
            'agency_id' => null,
            'make' => $make,
            'model' => $model,
            'year' => fake()->numberBetween(2015, 2024),
            'category' => fake()->randomElement($categories),
            'license_plate' => strtoupper(fake()->unique()->bothify('??####')),
            'rental_price_per_day' => fake()->randomFloat(2, 30, 300),
            'fuel_type' => fake()->randomElement($fuel_types),
            'transmission' => fake()->randomElement($transmissions),
            'seats' => fake()->numberBetween(2, 8),
            'features' => json_encode($features),
            'is_featured' => fake()->boolean(20),
            'is_active' => $isActive,
            'delisted_reason' => $delistedReason,
            'delisted_by' => $delistedBy,
            'delisted_at' => $delistedAt,
        ];
    }
}
