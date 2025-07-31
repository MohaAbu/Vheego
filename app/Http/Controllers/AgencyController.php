<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Agency;
use App\Models\Reservation;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewAgencySubmitted;
use Carbon\Carbon;

class AgencyController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        // Only renters without agency or with pending agency can access
        if ($user->user_type !== 'renter') {
            abort(403);
        }
        $agency = Agency::where('renter_id', $user->id)->first();
        if ($agency && $agency->verification_status !== 'pending') {
            return redirect()->route('dashboard');
        }
        return Inertia::render('Auth/AgencySetup', [
            'agency' => $agency,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->user_type !== 'renter') {
            abort(403);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = uniqid('agency_logo_') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/agency_logos', $filename);
            $data['logo'] = $filename;
        }
        
        $agency = Agency::updateOrCreate(
            ['renter_id' => $user->id],
            array_merge($data, [
                'verification_status' => 'pending',
                'submitted_at' => now(),
            ])
        );
        // Notify all admins
        $admins = \App\Models\User::where('user_type', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewAgencySubmitted($agency, $user));
        }
        return redirect('/')->with('success', 'Agency information submitted successfully! We will review your application and notify you via email.');
    }

    public function show($id)
    {
        $agency = Agency::with(['cars.images'])->findOrFail($id);
        return \Inertia\Inertia::render('Agency/Profile', [
            'agency' => $agency,
        ]);
    }

    public function status()
    {
        $user = Auth::user();
        $agency = $user->agency;
        return inertia('Agency/Status', [
            'agency' => $agency,
        ]);
    }

    // Public agency browsing for all users
    public function publicIndex()
    {
        $agencies = \App\Models\Agency::where('verification_status', 'approved')
            ->withCount('cars')
            ->orderByDesc('rating')
            ->get();
        return \Inertia\Inertia::render('Agency/Browse', [
            'agencies' => $agencies,
        ]);
    }

    // Agency management methods for approved renters

    /**
     * Get the verified agency for the current user
     */
    protected function getVerifiedAgency()
    {
        $user = Auth::user();
        $agency = Agency::where('renter_id', $user->id)->where('verification_status', 'approved')->first();
        
        if (!$agency) {
            abort(403, 'Only verified agencies can access this feature.');
        }
        
        return $agency;
    }

    /**
     * Show agency bookings/reservations management
     */
    public function bookings(Request $request)
    {
        $agency = $this->getVerifiedAgency();
        $carIds = $agency->cars->pluck('id');
        
        // Get reservations with filters
        $query = Reservation::whereIn('car_id', $carIds)
            ->with(['car', 'customer'])
            ->orderBy('created_at', 'desc');
        
        // Apply status filter if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Apply date filter if provided
        if ($request->has('date_from') && $request->date_from) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->where('end_date', '<=', $request->date_to);
        }
        
        $reservations = $query->paginate(20);
        
        // Calculate stats
        $totalReservations = Reservation::whereIn('car_id', $carIds)->count();
        $pendingReservations = Reservation::whereIn('car_id', $carIds)->where('status', 'pending')->count();
        $confirmedReservations = Reservation::whereIn('car_id', $carIds)->where('status', 'confirmed')->count();
        $completedReservations = Reservation::whereIn('car_id', $carIds)->where('status', 'completed')->count();
        
        // Monthly revenue
        $currentMonth = Carbon::now()->startOfMonth();
        $monthlyRevenue = Reservation::whereIn('car_id', $carIds)
            ->where('status', 'completed')
            ->where('created_at', '>=', $currentMonth)
            ->sum('total_price');
        
        return Inertia::render('Agency/Bookings', [
            'agency' => $agency,
            'reservations' => $reservations,
            'stats' => [
                'total' => $totalReservations,
                'pending' => $pendingReservations,
                'confirmed' => $confirmedReservations,
                'completed' => $completedReservations,
                'monthlyRevenue' => $monthlyRevenue,
            ],
            'filters' => $request->only(['status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show agency analytics and reports
     */
    public function analytics()
    {
        $agency = $this->getVerifiedAgency();
        $carIds = $agency->cars->pluck('id');
        
        // Revenue analytics
        $currentMonth = Carbon::now();
        $lastMonth = Carbon::now()->subMonth();
        $last6Months = Carbon::now()->subMonths(6);
        
        $currentMonthRevenue = Reservation::whereIn('car_id', $carIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$currentMonth->startOfMonth(), $currentMonth->endOfMonth()])
            ->sum('total_price');
            
        $lastMonthRevenue = Reservation::whereIn('car_id', $carIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$lastMonth->startOfMonth(), $lastMonth->endOfMonth()])
            ->sum('total_price');
        
        // Monthly revenue trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = Reservation::whereIn('car_id', $carIds)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$month->startOfMonth(), $month->endOfMonth()])
                ->sum('total_price');
            
            $monthlyTrend[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue,
            ];
        }
        
        // Top performing cars
        $topCars = Car::whereIn('id', $carIds)
            ->with(['reservations' => function($query) {
                $query->where('status', 'completed');
            }])
            ->get()
            ->map(function($car) {
                return [
                    'id' => $car->id,
                    'name' => $car->make . ' ' . $car->model,
                    'bookings' => $car->reservations->count(),
                    'revenue' => $car->reservations->sum('total_price'),
                ];
            })
            ->sortByDesc('revenue')
            ->take(5)
            ->values();
        
        // Booking status distribution
        $bookingStats = [
            'pending' => Reservation::whereIn('car_id', $carIds)->where('status', 'pending')->count(),
            'confirmed' => Reservation::whereIn('car_id', $carIds)->where('status', 'confirmed')->count(),
            'completed' => Reservation::whereIn('car_id', $carIds)->where('status', 'completed')->count(),
            'cancelled' => Reservation::whereIn('car_id', $carIds)->where('status', 'cancelled')->count(),
        ];
        
        return Inertia::render('Agency/Analytics', [
            'agency' => $agency,
            'revenue' => [
                'current_month' => $currentMonthRevenue,
                'last_month' => $lastMonthRevenue,
                'growth' => $lastMonthRevenue > 0 ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0,
                'monthly_trend' => $monthlyTrend,
            ],
            'top_cars' => $topCars,
            'booking_stats' => $bookingStats,
            'fleet_size' => $agency->cars->count(),
            'active_cars' => $agency->cars->where('is_active', true)->count(),
        ]);
    }

    /**
     * Show agency settings
     */
    public function settings()
    {
        $agency = $this->getVerifiedAgency();
        
        return Inertia::render('Agency/Settings', [
            'agency' => $agency,
        ]);
    }

    /**
     * Update agency settings
     */
    public function updateSettings(Request $request)
    {
        $agency = $this->getVerifiedAgency();
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($agency->logo) {
                \Storage::disk('public')->delete('agency_logos/' . $agency->logo);
            }
            
            $file = $request->file('logo');
            $filename = uniqid('agency_logo_') . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/agency_logos', $filename);
            $data['logo'] = $filename;
        }
        
        $agency->update($data);
        
        return redirect()->back()->with('success', 'Agency settings updated successfully!');
    }
}
