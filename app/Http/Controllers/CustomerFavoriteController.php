<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerFavorite;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;

class CustomerFavoriteController extends Controller
{
    // List all favorite cars for the current customer
    public function index()
    {
        $user = Auth::user();
        $favorites = $user->favorites()->with('car.images', 'car.agency')->get()->pluck('car');
        return response()->json($favorites);
    }

    // Add a car to favorites
    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'car_id' => 'required|exists:cars,id',
        ]);
        $carId = $request->car_id;
        // Prevent duplicate
        if ($user->favorites()->where('car_id', $carId)->exists()) {
            return response()->json(['message' => 'Already in favorites.'], 409);
        }
        $favorite = CustomerFavorite::create([
            'customer_id' => $user->id,
            'car_id' => $carId,
        ]);
        return response()->json($favorite, 201);
    }

    // Remove a car from favorites
    public function destroy($carId)
    {
        $user = Auth::user();
        $deleted = CustomerFavorite::where('customer_id', $user->id)
            ->where('car_id', $carId)
            ->delete();
        if ($deleted) {
            return response()->json(['message' => 'Removed from favorites.']);
        }
        return response()->json(['message' => 'Not found in favorites.'], 404);
    }
} 