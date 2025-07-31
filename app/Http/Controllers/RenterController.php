<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Agency;
use App\Models\Car;
use App\Models\Reservation;
use Carbon\Carbon;

class RenterController extends Controller
{
    public function dashboard()
    {
        $user = request()->user();
        $agency = Agency::where('renter_id', $user->id)->first();
        
        // Get renter's cars through agency relationship
        $cars = collect();
        $carIds = collect();
        
        if ($agency) {
            $cars = $agency->cars;
            $carIds = $cars->pluck('id');
        }
        
        // Calculate statistics
        $totalCars = $cars->count();
        $activeCars = $cars->where('is_active', true)->count();
        
        // Get reservations for renter's cars
        $reservations = Reservation::whereIn('car_id', $carIds)->get();
        $totalBookings = $reservations->count();
        $pendingBookings = $reservations->where('status', 'pending')->count();
        $completedBookings = $reservations->where('status', 'completed')->count();
        
        // Calculate monthly revenue
        $currentMonth = Carbon::now()->startOfMonth();
        $monthlyRevenue = $reservations
            ->where('status', 'completed')
            ->filter(function($reservation) use ($currentMonth) {
                return Carbon::parse($reservation->created_at)->gte($currentMonth);
            })
            ->sum('total_price');
        
        // Get recent bookings (last 5)
        $recentBookings = Reservation::whereIn('car_id', $carIds)
            ->with(['car', 'customer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'customer_name' => $booking->customer->name ?? 'Unknown Customer',
                    'car' => [
                        'make' => $booking->car->make,
                        'model' => $booking->car->model,
                        'year' => $booking->car->year,
                    ],
                    'start_date' => $booking->start_date,
                    'end_date' => $booking->end_date,
                    'total_price' => $booking->total_price,
                    'status' => $booking->status,
                ];
            });
        
        // Get top performing cars
        $topCars = collect();
        
        if ($agency && $carIds->isNotEmpty()) {
            $topCars = Car::whereIn('id', $carIds)
                ->with(['reservations' => function($query) {
                    $query->where('status', 'completed');
                }, 'images'])
                ->get()
                ->map(function($car) {
                    return [
                        'id' => $car->id,
                        'make' => $car->make,
                        'model' => $car->model,
                        'year' => $car->year,
                        'bookings_count' => $car->reservations->count(),
                        'revenue' => $car->reservations->sum('total_price'),
                        'images' => $car->images,
                    ];
                })
                ->sortByDesc('revenue')
                ->take(3)
                ->values();
        }
        
        $stats = [
            'totalCars' => $totalCars,
            'activeCars' => $activeCars,
            'totalBookings' => $totalBookings,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingBookings' => $pendingBookings,
            'completedBookings' => $completedBookings,
        ];
        
        return Inertia::render('Dashboard/RenterDashboard', [
            'user' => $user,
            'agency' => $agency,
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'topCars' => $topCars,
        ]);
    }
}
