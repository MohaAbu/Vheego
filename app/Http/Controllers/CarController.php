<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Agency;
use App\Models\CarImage;
use App\Models\CarUnavailability;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CarController extends Controller
{
    // Helper: Get verified agency for current user
    protected function getVerifiedAgency()
    {
        $user = Auth::user();
        $agency = Agency::where('renter_id', $user->id)->where('verification_status', 'approved')->first();
        if (!$agency) {
            abort(403, 'Only verified renters can manage cars.');
        }
        return $agency;
    }

    // List all cars for current renter
    public function index()
    {
        $agency = $this->getVerifiedAgency();
        $cars = Car::with('images', 'unavailabilities')->where('agency_id', $agency->id)->get();
        $user = Auth::user();
        // Add is_favorited property for each car if user is a customer
        if ($user && $user->user_type === 'customer') {
            $favoriteCarIds = $user->favorites()->pluck('car_id')->toArray();
            foreach ($cars as $car) {
                $car->is_favorited = in_array($car->id, $favoriteCarIds);
            }
        } else {
            foreach ($cars as $car) {
                $car->is_favorited = false;
            }
        }
        $total = $cars->count();
        $active = $cars->where('is_active', true)->count();
        return Inertia::render('Cars/Index', [
            'cars' => $cars,
            'stats' => [
                'total' => $total,
                'active' => $active,
            ],
        ]);
    }

    // Show form for creating a new car (API: return agency info)
    public function create()
    {
        $agency = $this->getVerifiedAgency();
        return Inertia::render('Cars/Create', [
            'agency' => $agency
        ]);
    }

    // Store a new car
    public function store(Request $request)
    {
        $agency = $this->getVerifiedAgency();
        $data = $request->validate([
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'required|in:economy,luxury,suv,sedan,hatchback,convertible',
            'license_plate' => 'required|string|max:50|unique:cars',
            'rental_price_per_day' => 'required|numeric|min:0',
            'fuel_type' => 'required|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'required|in:manual,automatic',
            'seats' => 'required|integer|min:1|max:20',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'images.*' => 'image|max:4096',
        ]);
        $car = Car::create(array_merge($data, [
            'agency_id' => $agency->id,
            'features' => json_encode($data['features'] ?? []),
        ]));
        // Handle images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $filename = uniqid('car_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/car_images', $filename);
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => 'car_images/' . $filename, // Save relative path
                    'is_primary' => $i === 0,
                ]);
            }
        }
        return redirect()->route('cars.index')->with('success', 'Car created successfully!');
    }

    // Show a car (must belong to current renter)
    public function show($id)
    {
        $agency = $this->getVerifiedAgency();
        $car = Car::with('images', 'unavailabilities')->where('agency_id', $agency->id)->findOrFail($id);
        $user = Auth::user();
        if ($user && $user->user_type === 'customer') {
            $car->is_favorited = $user->favorites()->where('car_id', $car->id)->exists();
        } else {
            $car->is_favorited = false;
        }
        return Inertia::render('Cars/Show', [
            'car' => $car
        ]);
    }

    public function edit($id)
    {
        $agency = $this->getVerifiedAgency();
        $car = Car::with('images')->where('agency_id', $agency->id)->findOrFail($id);
        return Inertia::render('Cars/Edit', [
            'car' => $car
        ]);
    }

    // Update a car
    public function update(Request $request, $id)
    {
        $agency = $this->getVerifiedAgency();
        $car = Car::where('agency_id', $agency->id)->findOrFail($id);

        // Get all input data
        $input = $request->all();
        
        // Handle features if it's a string (from JSON)
        if (isset($input['features']) && is_string($input['features'])) {
            $input['features'] = json_decode($input['features'], true) ?? [];
        }
        
        // Handle remove_image_ids if it's a string (from JSON)
        if (isset($input['remove_image_ids']) && is_string($input['remove_image_ids'])) {
            $input['remove_image_ids'] = json_decode($input['remove_image_ids'], true) ?? [];
        }

        // Validate the input
        $data = validator($input, [
            'make' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'required|in:economy,luxury,suv,sedan,hatchback,convertible',
            'license_plate' => 'required|string|max:50|unique:cars,license_plate,' . $car->id,
            'rental_price_per_day' => 'required|numeric|min:0',
            'fuel_type' => 'required|in:gasoline,diesel,electric,hybrid',
            'transmission' => 'required|in:manual,automatic',
            'seats' => 'required|integer|min:1|max:20',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'images' => 'nullable|array',
            'images.*' => 'image|max:4096',
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer|exists:car_images,id',
            'primary_image_id' => 'nullable|integer|exists:car_images,id',
        ])->validate();

        // Update the car
        $car->update([
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => $data['year'],
            'category' => $data['category'],
            'license_plate' => $data['license_plate'],
            'rental_price_per_day' => $data['rental_price_per_day'],
            'fuel_type' => $data['fuel_type'],
            'transmission' => $data['transmission'],
            'seats' => $data['seats'],
            'features' => json_encode($data['features'] ?? []),
        ]);

        // Remove selected images
        if (!empty($data['remove_image_ids'])) {
            $imagesToRemove = CarImage::where('car_id', $car->id)
                ->whereIn('id', $data['remove_image_ids'])
                ->get();
            
            foreach ($imagesToRemove as $img) {
                if ($img->image_path) {
                    Storage::disk('public')->delete($img->image_path);
                }
                $img->delete();
            }
        }

        // Handle new images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = uniqid('car_') . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/car_images', $filename);
                CarImage::create([
                    'car_id' => $car->id,
                    'image_path' => 'car_images/' . $filename, // Save relative path
                    'is_primary' => false,
                ]);
            }
        }

        // Update primary image
        if (!empty($data['primary_image_id'])) {
            // Reset all images to not primary
            CarImage::where('car_id', $car->id)->update(['is_primary' => false]);
            // Set the selected image as primary
            CarImage::where('id', $data['primary_image_id'])
                ->where('car_id', $car->id)
                ->update(['is_primary' => true]);
        }

        return redirect()->route('cars.index')->with('success', 'Car updated successfully!');
    }

    // Delete a car image
    public function destroyImage($id)
    {
        $user = Auth::user();
        $image = CarImage::findOrFail($id);
        $car = Car::findOrFail($image->car_id);
        $agency = Agency::where('renter_id', $user->id)->where('verification_status', 'approved')->first();
        if (!$agency || $car->agency_id !== $agency->id) {
            abort(403, 'Unauthorized');
        }
        if ($image->image_path) {
            Storage::disk('public')->delete($image->image_path);
        }
        $image->delete();
        return response()->json(['success' => true]);
    }

    // Delete/deactivate a car
    public function destroy($id)
    {
        $agency = $this->getVerifiedAgency();
        $car = Car::where('agency_id', $agency->id)->findOrFail($id);
        $car->delete();
        return redirect()->route('cars.index')->with('success', 'Car deleted successfully!');
    }

    // Set car unavailability dates
    public function setUnavailability(Request $request, $id)
    {
        $agency = $this->getVerifiedAgency();
        $car = Car::where('agency_id', $agency->id)->findOrFail($id);
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|in:reservation,maintenance,renter_blocked',
        ]);
        $unavailability = CarUnavailability::create([
            'car_id' => $car->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'created_by' => Auth::id(),
        ]);
        return response()->json($unavailability);
    }

    // Public car browsing for all users
    public function publicIndex(Request $request)
    {
        $query = Car::with(['images', 'agency'])
            ->where('is_active', true);

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by agency if provided
        if ($request->filled('agency_id')) {
            $query->where('agency_id', $request->agency_id);
        }

        // Filter by price range if provided
        if ($request->filled('min_price')) {
            $query->where('rental_price_per_day', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('rental_price_per_day', '<=', $request->max_price);
        }

        // Filter by fuel type if provided
        if ($request->filled('fuel_type')) {
            $query->where('fuel_type', $request->fuel_type);
        }

        // Filter by transmission if provided
        if ($request->filled('transmission')) {
            $query->where('transmission', $request->transmission);
        }

        // Filter by seats if provided
        if ($request->filled('seats')) {
            if ($request->seats == '8') {
                $query->where('seats', '>=', 8);
            } else {
                $query->where('seats', $request->seats);
            }
        }

        // Exclude specific car (for related cars)
        if ($request->filled('exclude')) {
            $query->where('id', '!=', $request->exclude);
        }

        // Filter by date availability if provided
        if ($request->filled(['start_date', 'end_date'])) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Exclude cars that have reservations overlapping with requested dates
            $query->whereNotExists(function ($subquery) use ($startDate, $endDate) {
                $subquery->select(DB::raw(1))
                    ->from('reservations')
                    ->whereColumn('reservations.car_id', 'cars.id')
                    ->where('status', '!=', 'cancelled') // Exclude all reservations except cancelled ones
                    ->where(function ($dateQuery) use ($startDate, $endDate) {
                        // Check for any date overlap using simpler logic
                        $dateQuery->where('start_date', '<=', $endDate)
                                 ->where('end_date', '>=', $startDate);
                    });
            });

            // Exclude cars that have unavailability periods overlapping with requested dates
            $query->whereNotExists(function ($subquery) use ($startDate, $endDate) {
                $subquery->select(DB::raw(1))
                    ->from('car_unavailabilities')
                    ->whereColumn('car_unavailabilities.car_id', 'cars.id')
                    ->where(function ($dateQuery) use ($startDate, $endDate) {
                        // Check for any date overlap
                        $dateQuery->where('start_date', '<=', $endDate)
                                 ->where('end_date', '>=', $startDate);
                    });
            });
        }

        // Handle custom limit for related cars API
        $limit = $request->filled('limit') ? (int)$request->limit : 10;
        $cars = $query->paginate($limit);
        
        // Parse features JSON and add favorite status for each car
        $user = Auth::user();
        $favoriteCarIds = [];
        
        if ($user && $user->user_type === 'customer') {
            $favoriteCarIds = $user->favorites()->pluck('car_id')->toArray();
        }
        
        $cars->getCollection()->transform(function ($car) use ($favoriteCarIds) {
            if ($car->features && is_string($car->features)) {
                $car->features = json_decode($car->features, true) ?: [];
            }
            $car->is_favorited = in_array($car->id, $favoriteCarIds);
            return $car;
        });
        
        // Append current filters to pagination links
        $cars->appends($request->only([
            'start_date', 'end_date', 'category', 'agency_id', 'location', 
            'min_price', 'max_price', 'fuel_type', 'transmission', 'seats'
        ]));

        // Get filter options
        $agencies = \App\Models\Agency::where('verification_status', 'approved')
                                    ->select('id', 'name')
                                    ->orderBy('name')
                                    ->get();

        return \Inertia\Inertia::render('Cars/Browse', [
            'cars' => $cars,
            'filters' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'category' => $request->category,
                'agency_id' => $request->agency_id,
                'location' => $request->location,
                'min_price' => $request->min_price,
                'max_price' => $request->max_price,
                'fuel_type' => $request->fuel_type,
                'transmission' => $request->transmission,
                'seats' => $request->seats,
            ],
            'agencies' => $agencies,
            'categories' => ['economy', 'luxury', 'suv', 'sedan', 'hatchback', 'convertible'],
            'locations' => [], // You can populate this with actual locations if needed
        ]);
    }

    // Public car details view for guests and authenticated users
    public function publicShow($id)
    {
        $car = Car::with(['images', 'agency'])
            ->where('is_active', true)
            ->findOrFail($id);

        $user = Auth::user();
        
        // Add favorite status for authenticated customers
        if ($user && $user->user_type === 'customer') {
            $car->is_favorited = $user->favorites()->where('car_id', $car->id)->exists();
        } else {
            $car->is_favorited = false;
        }

        // Parse features JSON if it's stored as string
        if ($car->features && is_string($car->features)) {
            $car->features = json_decode($car->features, true) ?: [];
        }

        return Inertia::render('Cars/PublicShow', [
            'car' => $car,
        ]);
    }

    // Get related cars for a specific car
    public function relatedCars($carId)
    {
        $currentCar = Car::findOrFail($carId);
        
        // First try to get cars from the same agency
        $relatedCars = Car::with(['images', 'agency'])
            ->where('is_active', true)
            ->where('id', '!=', $carId)
            ->where('agency_id', $currentCar->agency_id)
            ->limit(4)
            ->get();
        
        // If we don't have enough cars from the same agency, get cars from the same category
        if ($relatedCars->count() < 4) {
            $remainingSlots = 4 - $relatedCars->count();
            $categoryCarIds = $relatedCars->pluck('id')->push($carId)->toArray();
            
            $categoryCars = Car::with(['images', 'agency'])
                ->where('is_active', true)
                ->where('category', $currentCar->category)
                ->whereNotIn('id', $categoryCarIds)
                ->limit($remainingSlots)
                ->get();
                
            $relatedCars = $relatedCars->merge($categoryCars);
        }
        
        // Parse features JSON for each car
        $relatedCars->transform(function ($car) {
            if ($car->features && is_string($car->features)) {
                $car->features = json_decode($car->features, true) ?: [];
            }
            return $car;
        });
        
        return response()->json([
            'data' => $relatedCars,
            'total' => $relatedCars->count()
        ]);
    }

    // Check car availability for specific date range
    public function checkAvailability(Request $request, Car $car)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Check if user already has a pending/active reservation for this car
        if ($request->user()) {
            $userHasReservation = $car->reservations()
                ->where('customer_id', $request->user()->id)
                ->whereIn('status', ['pending_payment', 'active', 'confirmed'])
                ->exists();
            
            if ($userHasReservation) {
                return response()->json([
                    'available' => false,
                    'message' => 'You already have an active reservation for this car.'
                ]);
            }
        }

        $available = $car->isAvailable($startDate, $endDate);

        if (!$available) {
            return response()->json([
                'available' => false,
                'message' => 'Car is not available for the selected dates. Another booking or maintenance period conflicts with your dates.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message' => 'Car is available for the selected dates.'
        ]);
    }
}
