<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Agency;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Notifications\AgencyStatusChanged;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Get statistics for dashboard
        $totalUsers = User::count();
        $totalCars = \App\Models\Car::count();
        $totalReservations = \App\Models\Reservation::count();
        $totalRevenue = \App\Models\Reservation::where('status', 'completed')->sum('total_price');
        
        // Today's stats
        $todayReservations = \App\Models\Reservation::whereDate('created_at', today())->count();
        $todayRevenue = \App\Models\Reservation::whereDate('created_at', today())->sum('total_price');
        
        // This week's stats
        $weekReservations = \App\Models\Reservation::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $weekRevenue = \App\Models\Reservation::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total_price');
        
        // This month's stats
        $monthReservations = \App\Models\Reservation::whereMonth('created_at', now()->month)->count();
        $monthRevenue = \App\Models\Reservation::whereMonth('created_at', now()->month)->sum('total_price');
        
        $pendingAgencies = Agency::with('renter')
            ->where('verification_status', 'pending')
            ->orderBy('submitted_at', 'asc')
            ->get();
            
        $users = User::with('agency')->get();
        $cars = \App\Models\Car::with('agency', 'images')->get();
        $reservations = \App\Models\Reservation::with(['customer', 'car.agency'])->latest()->take(10)->get();
        
        return \Inertia\Inertia::render('Dashboard/AdminDashboard', [
            'user' => request()->user(),
            'stats' => [
                'totalUsers' => $totalUsers,
                'totalCars' => $totalCars,
                'totalReservations' => $totalReservations,
                'totalRevenue' => $totalRevenue,
                'todayReservations' => $todayReservations,
                'todayRevenue' => $todayRevenue,
                'weekReservations' => $weekReservations,
                'weekRevenue' => $weekRevenue,
                'monthReservations' => $monthReservations,
                'monthRevenue' => $monthRevenue,
            ],
            'pendingAgencies' => $pendingAgencies,
            'users' => $users,
            'cars' => $cars,
            'reservations' => $reservations,
        ]);
    }

    public function approveAgency(Request $request, Agency $agency)
    {
        $agency->update([
            'verification_status' => 'approved',
            'admin_feedback' => $request->input('admin_feedback'),
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);
        // Notify renter
        $agency->renter->notify(new AgencyStatusChanged($agency));
        return back()->with('success', 'Agency approved.');
    }

    public function rejectAgency(Request $request, Agency $agency)
    {
        $request->validate([
            'admin_feedback' => 'required|string|max:1000',
        ]);
        $agency->update([
            'verification_status' => 'rejected',
            'admin_feedback' => $request->input('admin_feedback'),
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);
        // Notify renter
        $agency->renter->notify(new AgencyStatusChanged($agency));
        return back()->with('success', 'Agency rejected with feedback.');
    }

    public function banUser(Request $request, $userId)
    {
        $admin = $request->user();
        if ($admin->user_type !== 'admin' || $admin->id == $userId) {
            abort(403);
        }
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        $user = \App\Models\User::findOrFail($userId);
        $user->update([
            'is_banned' => true,
            'banned_reason' => $request->reason,
            'banned_by' => $admin->id,
            'banned_at' => now(),
        ]);
        return back()->with('success', 'User banned.');
    }

    public function unbanUser(Request $request, $userId)
    {
        $admin = $request->user();
        if ($admin->user_type !== 'admin' || $admin->id == $userId) {
            abort(403);
        }
        $user = \App\Models\User::findOrFail($userId);
        $user->update([
            'is_banned' => false,
            'banned_reason' => null,
            'banned_by' => null,
            'banned_at' => null,
        ]);
        return back()->with('success', 'User unbanned.');
    }

    public function delistCar(Request $request, $carId)
    {
        $admin = $request->user();
        if ($admin->user_type !== 'admin') {
            abort(403);
        }
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);
        $car = \App\Models\Car::findOrFail($carId);
        $car->update([
            'is_active' => false,
            'delisted_reason' => $request->reason,
            'delisted_by' => $admin->id,
            'delisted_at' => now(),
        ]);
        return back()->with('success', 'Car delisted.');
    }

    public function relistCar(Request $request, $carId)
    {
        $admin = $request->user();
        if ($admin->user_type !== 'admin') {
            abort(403);
        }
        
        $car = \App\Models\Car::findOrFail($carId);
        $car->update([
            'is_active' => true,
            'delisted_reason' => null,
            'delisted_by' => null,
            'delisted_at' => null,
        ]);
        return back()->with('success', 'Car relisted successfully.');
    }

    public function users(Request $request)
    {
        $query = User::with('agency');
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Apply status filter
        if ($request->filled('filter') && $request->filter !== 'all') {
            switch ($request->filter) {
                case 'active':
                    $query->where('is_banned', false);
                    break;
                case 'banned':
                    $query->where('is_banned', true);
                    break;
                case 'admin':
                    $query->where('user_type', 'admin');
                    break;
                case 'renter':
                    $query->where('user_type', 'renter');
                    break;
                case 'customer':
                    $query->where('user_type', 'customer');
                    break;
            }
        }
        
        $users = $query->paginate(20)->withQueryString();
        
        $stats = [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_banned', false)->count(),
            'bannedUsers' => User::where('is_banned', true)->count(),
            'adminUsers' => User::where('user_type', 'admin')->count(),
            'renterUsers' => User::where('user_type', 'renter')->count(),
            'customerUsers' => User::where('user_type', 'customer')->count(),
        ];
        
        return Inertia::render('Admin/Users', [
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    public function agencies(Request $request)
    {
        $query = Agency::with(['renter', 'cars']);
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhereHas('renter', function($renterQuery) use ($search) {
                      $renterQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply status filter
        if ($request->filled('filter') && $request->filter !== 'all') {
            switch ($request->filter) {
                case 'pending':
                    $query->where('verification_status', 'pending');
                    break;
                case 'approved':
                    $query->where('verification_status', 'approved');
                    break;
                case 'rejected':
                    $query->where('verification_status', 'rejected');
                    break;
            }
        }
        
        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['name', 'verification_status', 'created_at', 'rating'])) {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        $agencies = $query->paginate(20)->withQueryString();
        
        // Add calculated fields
        $agencies->getCollection()->transform(function ($agency) {
            $agency->total_cars = $agency->cars->count();
            $agency->active_cars = $agency->cars->where('is_active', true)->count();
            $agency->logo_url = $agency->logo_url; // Trigger accessor
            return $agency;
        });
        
        $stats = [
            'totalAgencies' => Agency::count(),
            'pendingAgencies' => Agency::where('verification_status', 'pending')->count(),
            'approvedAgencies' => Agency::where('verification_status', 'approved')->count(),
            'rejectedAgencies' => Agency::where('verification_status', 'rejected')->count(),
            'totalCars' => \App\Models\Car::count(),
            'avgRating' => Agency::where('verification_status', 'approved')->avg('rating'),
        ];
        
        return Inertia::render('Admin/Agencies', [
            'agencies' => $agencies,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'filter' => $request->filter,
                'sort' => $sortBy,
                'direction' => $sortDirection,
            ],
        ]);
    }

    public function analytics()
    {
        $now = now();
        
        // Revenue analytics
        $totalRevenue = \App\Models\Reservation::where('status', 'completed')->sum('total_price');
        $monthlyRevenue = \App\Models\Reservation::where('status', 'completed')
            ->whereMonth('created_at', $now->month)
            ->sum('total_price');
        $weeklyRevenue = \App\Models\Reservation::where('status', 'completed')
            ->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()])
            ->sum('total_price');
        
        // Booking analytics
        $totalBookings = \App\Models\Reservation::count();
        $completedBookings = \App\Models\Reservation::where('status', 'completed')->count();
        $activeBookings = \App\Models\Reservation::where('status', 'active')->count();
        $cancelledBookings = \App\Models\Reservation::where('status', 'cancelled')->count();
        
        // Top agencies by revenue
        $topAgencies = Agency::join('cars', 'agencies.id', '=', 'cars.agency_id')
            ->join('reservations', 'cars.id', '=', 'reservations.car_id')
            ->where('reservations.status', 'completed')
            ->select('agencies.*', \DB::raw('SUM(reservations.total_price) as total_revenue'))
            ->groupBy('agencies.id')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();
        
        // Monthly revenue chart data (last 6 months)
        $revenueChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $revenue = \App\Models\Reservation::where('status', 'completed')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_price');
            
            $revenueChart[] = [
                'month' => $month->format('M Y'),
                'revenue' => $revenue,
            ];
        }
        
        return Inertia::render('Admin/Analytics', [
            'revenueStats' => [
                'total' => $totalRevenue,
                'monthly' => $monthlyRevenue,
                'weekly' => $weeklyRevenue,
            ],
            'bookingStats' => [
                'total' => $totalBookings,
                'completed' => $completedBookings,
                'active' => $activeBookings,
                'cancelled' => $cancelledBookings,
            ],
            'topAgencies' => $topAgencies,
            'revenueChart' => $revenueChart,
        ]);
    }

    public function reservations(Request $request)
    {
        $query = \App\Models\Reservation::with(['customer', 'car.agency']);
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('customer', function($customerQuery) use ($search) {
                    $customerQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('car', function($carQuery) use ($search) {
                    $carQuery->where('make', 'like', "%{$search}%")
                             ->orWhere('model', 'like', "%{$search}%");
                })
                ->orWhereHas('car.agency', function($agencyQuery) use ($search) {
                    $agencyQuery->where('name', 'like', "%{$search}%");
                });
            });
        }
        
        // Apply status filter
        if ($request->filled('filter') && $request->filter !== 'all') {
            switch ($request->filter) {
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'confirmed':
                    $query->where('status', 'confirmed');
                    break;
                case 'active':
                    $query->where('status', 'active');
                    break;
                case 'completed':
                    $query->where('status', 'completed');
                    break;
                case 'cancelled':
                    $query->where('status', 'cancelled');
                    break;
            }
        }
        
        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Apply sorting
        $sortBy = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortBy, ['created_at', 'start_date', 'end_date', 'total_price', 'status'])) {
            $query->orderBy($sortBy, $sortDirection);
        }
        
        // Handle per page selection with max limit of 100
        $perPage = $request->get('per_page', 20);
        $perPage = min(max((int)$perPage, 10), 100); // Between 10 and 100
        
        $reservations = $query->paginate($perPage)->withQueryString();
        
        $stats = [
            'totalReservations' => \App\Models\Reservation::count(),
            'pendingReservations' => \App\Models\Reservation::where('status', 'pending')->count(),
            'confirmedReservations' => \App\Models\Reservation::where('status', 'confirmed')->count(),
            'activeReservations' => \App\Models\Reservation::where('status', 'active')->count(),
            'completedReservations' => \App\Models\Reservation::where('status', 'completed')->count(),
            'cancelledReservations' => \App\Models\Reservation::where('status', 'cancelled')->count(),
            'totalRevenue' => \App\Models\Reservation::where('status', 'completed')->sum('total_price'),
            'monthlyRevenue' => \App\Models\Reservation::where('status', 'completed')->whereMonth('created_at', now()->month)->sum('total_price'),
        ];
        
        return Inertia::render('Admin/Reservations', [
            'reservations' => $reservations,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'filter' => $request->filter,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'sort' => $sortBy,
                'direction' => $sortDirection,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function settings()
    {
        // Get platform settings from database
        $settings = [
            'platform_name' => Setting::get('platform_name', config('app.name', 'Vheego')),
            'support_email' => Setting::get('support_email', config('mail.from.address', 'support@vheego.com')),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'allow_registrations' => Setting::get('allow_registrations', true),
            'auto_approve_agencies' => Setting::get('auto_approve_agencies', false),
            'commission_rate' => Setting::get('commission_rate', 10),
            'logo' => Setting::get('logo', null),
        ];
        
        return Inertia::render('Admin/Settings', [
            'settings' => $settings,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'platform_name' => 'required|string|max:255',
            'support_email' => 'required|email|max:255',
            'maintenance_mode' => 'boolean',
            'allow_registrations' => 'boolean',
            'auto_approve_agencies' => 'boolean',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoFile = $request->file('logo');
            $logoPath = $logoFile->store('logos', 'public');
            
            // Delete old logo if exists
            $oldLogo = Setting::get('logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            
            Setting::set('logo', $logoPath, 'string', 'Application logo');
        }

        // Save all settings to database
        Setting::set('platform_name', $request->platform_name, 'string', 'Platform display name');
        Setting::set('support_email', $request->support_email, 'string', 'Support contact email');
        Setting::set('maintenance_mode', $request->boolean('maintenance_mode'), 'boolean', 'Maintenance mode status');
        Setting::set('allow_registrations', $request->boolean('allow_registrations'), 'boolean', 'Allow new user registrations');
        Setting::set('auto_approve_agencies', $request->boolean('auto_approve_agencies'), 'boolean', 'Auto-approve agency applications');
        Setting::set('commission_rate', $request->commission_rate, 'float', 'Platform commission rate percentage');

        // Clear all settings cache to ensure immediate updates
        Setting::clearCache();
        
        // Also clear Laravel application cache to ensure immediate updates
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        return back()->with('success', 'Settings updated successfully!');
    }
}
