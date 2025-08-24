<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerFavorite;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerFavoriteController extends Controller
{
    // List all favorite cars for the current customer
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->user_type !== 'customer') {
            abort(403, 'Only customers can have favorites.');
        }
        
        $favorites = $user->favorites()->with('car.images', 'car.agency')->get()->pluck('car');
        
        if ($request->wantsJson()) {
            return response()->json($favorites);
        }
        
        return Inertia::render('Customer/Favorites', [
            'favorites' => $favorites
        ]);
    }

    // Add a car to favorites
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->user_type !== 'customer') {
            abort(403, 'Only customers can add favorites.');
        }
        
        $request->validate([
            'car_id' => 'required|exists:cars,id',
        ]);
        $carId = $request->car_id;
        
        // Prevent duplicate
        if ($user->favorites()->where('car_id', $carId)->exists()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Already in favorites.'], 409);
            }
            return back()->with('error', 'Already in favorites.');
        }
        
        $favorite = CustomerFavorite::create([
            'customer_id' => $user->id,
            'car_id' => $carId,
        ]);
        
        if ($request->wantsJson()) {
            return response()->json($favorite, 201);
        }
        
        return back()->with('success', 'Added to favorites!');
    }

    // Remove a car from favorites
    public function destroy(Request $request, $carId)
    {
        $user = Auth::user();
        
        if ($user->user_type !== 'customer') {
            abort(403, 'Only customers can remove favorites.');
        }
        
        $deleted = CustomerFavorite::where('customer_id', $user->id)
            ->where('car_id', $carId)
            ->delete();
            
        if ($deleted) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Removed from favorites.']);
            }
            return back()->with('success', 'Removed from favorites.');
        }
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Not found in favorites.'], 404);
        }
        return back()->with('error', 'Not found in favorites.');
    }
} 