<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Reservation;
use App\Models\Car;
use App\Models\Agency;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Submit a review for a completed reservation or direct car review
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Support both reservation-based and direct car reviews
        if ($request->filled('reservation_id')) {
            // Original reservation-based review system
            $request->validate([
                'reservation_id' => 'required|exists:reservations,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);
            
            $reservation = Reservation::findOrFail($request->reservation_id);
            // Only allow review if reservation is completed and belongs to user
            if ($reservation->customer_id !== $user->id || $reservation->status !== 'completed') {
                return response()->json(['message' => 'You can only review your own completed reservations.'], 403);
            }
            // Only one review per reservation
            if (Review::where('reservation_id', $reservation->id)->exists()) {
                return response()->json(['message' => 'You have already reviewed this reservation.'], 409);
            }
            
            $review = Review::create([
                'reservation_id' => $reservation->id,
                'customer_id' => $user->id,
                'agency_id' => $reservation->car->agency_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
        } else if ($request->filled('car_id')) {
            // New direct car review system
            $request->validate([
                'car_id' => 'required|exists:cars,id',
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'required|string|max:1000',
            ]);
            
            $car = Car::findOrFail($request->car_id);
            
            // Only customers can review
            if ($user->user_type !== 'customer') {
                return response()->json(['message' => 'Only customers can write reviews.'], 403);
            }
            
            // Check if user already reviewed this car (prevent multiple reviews per car per user)
            $existingReview = Review::where('customer_id', $user->id)
                ->where('car_id', $request->car_id)
                ->exists();
                
            if ($existingReview) {
                return response()->json(['message' => 'You have already reviewed this car.'], 409);
            }
            
            $review = Review::create([
                'car_id' => $request->car_id,
                'customer_id' => $user->id,
                'agency_id' => $car->agency_id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
        } else {
            return response()->json(['message' => 'Either reservation_id or car_id is required.'], 422);
        }
        
        return response()->json($review->load('customer'), 201);
    }

    // Get reviews for a car
    public function carReviews($carId)
    {
        $car = Car::findOrFail($carId);
        
        // Get both direct car reviews and reservation-based reviews
        $directReviews = Review::where('car_id', $carId)
            ->with('customer')
            ->latest()
            ->get();
            
        $reservationReviews = Review::whereHas('reservation', function($q) use ($carId) {
            $q->where('car_id', $carId);
        })
        ->with(['customer', 'reservation'])
        ->latest()
        ->get();
        
        // Combine all reviews
        $allReviews = $directReviews->merge($reservationReviews)->sortByDesc('created_at');
        
        // Debug: Let's see what we're returning
        \Log::info('Car Reviews Debug', [
            'car_id' => $carId,
            'direct_reviews_count' => $directReviews->count(),
            'reservation_reviews_count' => $reservationReviews->count(),
            'total_reviews' => $allReviews->count(),
            'sample_review' => $allReviews->first() ? [
                'id' => $allReviews->first()->id,
                'rating' => $allReviews->first()->rating,
                'comment' => $allReviews->first()->comment,
                'customer_name' => $allReviews->first()->customer->name ?? 'N/A'
            ] : null
        ]);
        
        return response()->json($allReviews->values());
    }

    // Get reviews for an agency
    public function agencyReviews($agencyId)
    {
        $agency = Agency::findOrFail($agencyId);
        $reviews = Review::where('agency_id', $agencyId)->with('customer')->latest()->get();
        return response()->json($reviews);
    }
} 