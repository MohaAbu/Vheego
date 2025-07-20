<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Agency;
use App\Models\Car;
use App\Models\Reservation;
use App\Models\Review;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $renters = User::factory(10)->renter()->create();
        $agencies = collect();
        foreach ($renters as $renter) {
            $agencies->push(Agency::factory()->create([
                'renter_id' => $renter->id,
                'verification_status' => fake()->randomElement(['approved', 'pending', 'rejected']),
            ]));
        }

        $customers = User::factory(30)->customer()->create();

        $cars = collect();
        foreach ($agencies as $agency) {
            $numCars = rand(10, 20);
            $newCars = Car::factory($numCars)->create([
                'agency_id' => $agency->id,
            ]);
            // Randomly mark some cars as special offers
            $specialOfferCount = rand(2, max(2, intval($numCars / 3)));
            $newCars->random($specialOfferCount)->each(function ($car) {
                $car->special_offer = true;
                $car->save();
            });
            $cars = $cars->merge($newCars);
        }

        $reservations = collect();
        foreach ($customers as $customer) {
            $numReservations = rand(5, 10);
            for ($i = 0; $i < $numReservations; $i++) {
                $car = $cars->random();
                $reservations->push(
                    Reservation::factory()->create([
                        'customer_id' => $customer->id,
                        'car_id' => $car->id,
                    ])
                );
            }
        }

        $reviewedReservations = $reservations->filter(function ($reservation) {
            return $reservation->status === 'completed';
        });
        foreach ($reviewedReservations as $reservation) {
            Review::factory()->create([
                'reservation_id' => $reservation->id,
                'customer_id' => $reservation->customer_id,
                'agency_id' => $reservation->car->agency_id,
            ]);
        }

        foreach ($agencies as $agency) {
            $agencyReviews = Review::where('agency_id', $agency->id)->get();
            $count = $agencyReviews->count();
            $avg = $count > 0 ? round($agencyReviews->avg('rating'), 2) : 0;
            $agency->update([
                'rating' => $avg,
                'total_reviews' => $count,
            ]);
        }
    }
}
