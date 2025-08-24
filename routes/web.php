<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerifyNewEmailController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\AdminController;
use App\Models\Car;
use App\Models\Agency;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AgencyController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    $categories = Car::select('category')->distinct()->pluck('category');
    $featuredCars = Car::whereHas('reviews', function ($q) {
        $q->where('rating', 5);
    })
    ->with('agency')
    ->withCount('reviews')
    ->withAvg('reviews', 'rating')
    ->orderByDesc('reviews_count')
    ->take(3)
    ->get()
    ->map(function ($car) {
        $car->average_rating = round($car->reviews_avg_rating, 1);
        return $car;
    });
    $topAgencies = Agency::orderByDesc('rating')->take(3)->get()->map(function ($agency) {
        return [
            'id' => $agency->id,
            'name' => $agency->name,
            'logo' => $agency->logo,
            'rating' => (float) $agency->rating, // ensure float
            'locations' => $agency->address,
            'cars' => $agency->cars()->count(),
        ];
    });
    $user = Auth::user();
    if ($user) {
        if ($user->user_type === 'renter') {
            $user = \App\Models\User::with('agency')->find($user->id);
        }
        $userArray = method_exists($user, 'toArray') ? $user->toArray() : (array) $user;
        $userArray['profile_picture_url'] = $user->profile_picture_url;
        if ($user->user_type === 'renter') {
            $userArray['agency'] = $user->agency ? (method_exists($user->agency, 'toArray') ? $user->agency->toArray() : (array) $user->agency) : null;
        }
    } else {
        $userArray = null;
    }
    $heroCar = Car::with('agency')
        ->withCount('reviews')
        ->withAvg('reviews', 'rating')
        ->orderByDesc('reviews_count')
        ->first();

    return Inertia::render('Welcome', [
        'categories' => $categories,
        'featuredCars' => $featuredCars,
        'topAgencies' => $topAgencies,
        'user' => $userArray,
        'heroCar' => $heroCar,
    ]);
});

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('reservations', App\Http\Controllers\ReservationController::class)->only(['index', 'show', 'create', 'store']);
    Route::get('reservations/{id}/payment', [App\Http\Controllers\ReservationController::class, 'paymentForm'])->name('reservations.payment');
    Route::post('reservations/{id}/payment', [App\Http\Controllers\ReservationController::class, 'processPayment'])->name('reservations.processPayment');
    Route::get('reservations/{id}/confirmation', [App\Http\Controllers\ReservationController::class, 'confirmation'])->name('reservations.confirmation');
    Route::patch('reservations/{id}/status', [App\Http\Controllers\ReservationController::class, 'updateStatus'])->name('reservations.updateStatus');
    // Customer favorites (wishlist)
    Route::get('/favorites', [\App\Http\Controllers\CustomerFavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites', [\App\Http\Controllers\CustomerFavoriteController::class, 'store'])->name('favorites.store');
    Route::delete('/favorites/{car}', [\App\Http\Controllers\CustomerFavoriteController::class, 'destroy'])->name('favorites.destroy');
});

Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Renter dashboard route
Route::middleware(['auth', 'role:renter'])->get('/renter/dashboard', [\App\Http\Controllers\RenterController::class, 'dashboard'])->name('dashboard.renter');

Route::middleware(['auth', 'role:renter'])->group(function () {
    Route::get('/agency/setup', [\App\Http\Controllers\AgencyController::class, 'create'])->name('agency.create');
    Route::post('/agency/setup', [\App\Http\Controllers\AgencyController::class, 'store'])->name('agency.store');
});

// Public cars browsing (must come first to avoid conflicts)
Route::get('/cars', [CarController::class, 'publicIndex'])->name('cars.public');

Route::middleware(['auth', 'approved.renter'])->group(function () {
    // Car management routes (only for approved renters) - using 'manage' prefix
    Route::resource('manage/cars', \App\Http\Controllers\CarController::class)->names([
        'index' => 'cars.index',
        'create' => 'cars.create', 
        'store' => 'cars.store',
        'show' => 'cars.show',
        'edit' => 'cars.edit',
        'update' => 'cars.update',
        'destroy' => 'cars.destroy'
    ]);
    // Car image deletion
    Route::delete('/car-images/{id}', [\App\Http\Controllers\CarController::class, 'destroyImage'])->name('car-images.destroy');
    
    // Agency-specific routes
    Route::prefix('agency')->name('agency.')->group(function () {
        Route::get('/bookings', [\App\Http\Controllers\AgencyController::class, 'bookings'])->name('bookings');
        Route::get('/analytics', [\App\Http\Controllers\AgencyController::class, 'analytics'])->name('analytics');
        Route::get('/settings', [\App\Http\Controllers\AgencyController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\AgencyController::class, 'updateSettings'])->name('settings.update');
    });
});
Route::get('/api/cars/related/{car}', [CarController::class, 'relatedCars'])->name('cars.related');
Route::get('/api/cars/{car}/availability', [CarController::class, 'checkAvailability'])->name('cars.availability');
Route::get('/cars/{car}', [CarController::class, 'publicShow'])->name('cars.publicShow');
Route::get('/agency/status', [AgencyController::class, 'status'])->name('agency.status')->middleware(['auth']);

// Public agency profile
Route::get('/agencies', [\App\Http\Controllers\AgencyController::class, 'publicIndex'])->name('agencies.public');
Route::get('/agencies/{agency}', [\App\Http\Controllers\AgencyController::class, 'show'])->name('agencies.show');

// Customer reviews
Route::middleware('auth')->post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
Route::get('/cars/{car}/reviews', [\App\Http\Controllers\ReviewController::class, 'carReviews'])->name('cars.reviews');
Route::get('/agencies/{agency}/reviews', [\App\Http\Controllers\ReviewController::class, 'agencyReviews'])->name('agencies.reviews');

// Fallback route for 404s
Route::fallback(function () {
    return Inertia::render('Errors/404');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard.admin');
    
    // Sidebar pages
    Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/agencies', [App\Http\Controllers\AdminController::class, 'agencies'])->name('admin.agencies');
    Route::get('/admin/reservations', [App\Http\Controllers\AdminController::class, 'reservations'])->name('admin.reservations');
    Route::get('/admin/analytics', [App\Http\Controllers\AdminController::class, 'analytics'])->name('admin.analytics');
    Route::get('/admin/settings', [App\Http\Controllers\AdminController::class, 'settings'])->name('admin.settings');
    Route::post('/admin/settings', [App\Http\Controllers\AdminController::class, 'updateSettings'])->name('admin.settings.update');
    
    // Agency management
    Route::post('/admin/agencies/{agency}/approve', [App\Http\Controllers\AdminController::class, 'approveAgency'])->name('admin.agencies.approve');
    Route::post('/admin/agencies/{agency}/reject', [App\Http\Controllers\AdminController::class, 'rejectAgency'])->name('admin.agencies.reject');
    
    // User management
    Route::post('/admin/users/{user}/ban', [App\Http\Controllers\AdminController::class, 'banUser'])->name('admin.users.ban');
    Route::post('/admin/users/{user}/unban', [App\Http\Controllers\AdminController::class, 'unbanUser'])->name('admin.users.unban');
    
    // Car management
    Route::post('/admin/cars/{car}/delist', [App\Http\Controllers\AdminController::class, 'delistCar'])->name('admin.cars.delist');
    Route::post('/admin/cars/{car}/relist', [App\Http\Controllers\AdminController::class, 'relistCar'])->name('admin.cars.relist');
});
