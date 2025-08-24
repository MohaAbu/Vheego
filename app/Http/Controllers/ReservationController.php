<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Car;
use App\Http\Requests\ReservationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReservationController extends Controller
{
    // List reservations for the current user (customer, renter, or admin)
    public function index(Request $request)
    {
        $user = $request->user();
        
        if ($user->user_type === 'customer') {
            // Customer: show their own reservations
            $reservations = Reservation::with(['car.agency', 'car.images', 'review'])
                ->where('customer_id', $user->id)
                ->orderByDesc('created_at')
                ->get();
            // Add reviewed property
            foreach ($reservations as $reservation) {
                $reservation->reviewed = $reservation->review()->exists();
            }
        } elseif ($user->user_type === 'renter') {
            // Renter: show reservations for their cars
            $agency = $user->agency;
            $reservations = $agency
                ? Reservation::with(['car.agency', 'car.images', 'customer', 'review'])
                    ->whereHas('car', function ($q) use ($agency) {
                        $q->where('agency_id', $agency->id);
                    })
                    ->orderByDesc('created_at')
                    ->get()
                : collect();
        } else {
            // Admin: show all reservations
            $reservations = Reservation::with(['car.agency', 'car.images', 'customer', 'review'])
                ->orderByDesc('created_at')
                ->get();
        }
        
        if ($request->wantsJson()) {
            return response()->json($reservations);
        }
        
        $viewName = $user->user_type === 'customer' ? 'Customer/Reservations' : 'Admin/Reservations';
        
        return Inertia::render($viewName, [
            'reservations' => $reservations
        ]);
    }

    // Show details for a single reservation
    public function show(Request $request, $id)
    {
        $reservation = Reservation::with(['car.agency', 'car.images', 'customer', 'review'])->findOrFail($id);
        $user = Auth::user();
        
        // Authorization: only customer, renter (of car), or admin can view
        if (
            ($user->user_type === 'customer' && $reservation->customer_id !== $user->id) ||
            ($user->user_type === 'renter' && $reservation->car->agency->renter_id !== $user->id) &&
            ($user->user_type !== 'admin')
        ) {
            abort(403, 'Unauthorized to view this reservation.');
        }
        
        if ($request->wantsJson()) {
            return response()->json($reservation);
        }
        
        return Inertia::render('Reservations/Show', [
            'reservation' => $reservation
        ]);
    }

    // Show form for creating a new reservation
    public function create(Request $request)
    {
        $car_id = $request->query('car_id');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');
        
        $car = null;
        if ($car_id) {
            $car = Car::with('agency')->findOrFail($car_id);
            
            // Check availability if dates provided
            if ($start_date && $end_date) {
                if (!$car->isAvailable($start_date, $end_date)) {
                    return response()->json(['message' => 'Car is not available for the selected dates.'], 422);
                }
                
                $days = (new \DateTime($end_date))->diff(new \DateTime($start_date))->days + 1;
                $total_price = $days * $car->rental_price_per_day;
                
                return response()->json([
                    'car' => $car,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'days' => $days,
                    'total_price' => $total_price
                ]);
            }
        }
        
        return response()->json(['car' => $car]);
    }

    // Create a new reservation (status: pending_payment)
    public function store(ReservationRequest $request)
    {
        $user = $request->user();
        $car = Car::findOrFail($request->car_id);
        // Double-check availability (in case of race condition)
        if (!$car->isAvailable($request->start_date, $request->end_date)) {
            return response()->json(['message' => 'Car is not available for the selected dates.'], 422);
        }
        $days = (new \DateTime($request->end_date))->diff(new \DateTime($request->start_date))->days + 1;
        $totalPrice = $days * $car->rental_price_per_day;
        $reservation = Reservation::create([
            'customer_id' => $user->id,
            'car_id' => $car->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_price' => $totalPrice,
            'status' => 'pending_payment',
        ]);
        // Redirect to payment page (for web), or return reservation (for API)
        if ($request->wantsJson()) {
            return response()->json($reservation, 201);
        }
        return redirect()->route('reservations.payment', $reservation->id)->with('success', 'Reservation created! Please complete payment.');
    }

    // Show mock payment form
    public function paymentForm(Request $request, $id)
    {
        $reservation = Reservation::with(['car.agency', 'car.images'])->findOrFail($id);
        $user = Auth::user();
        
        if ($reservation->customer_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }
        
        if ($request->wantsJson()) {
            return response()->json(['reservation' => $reservation]);
        }
        
        return Inertia::render('Reservations/Payment', [
            'reservation' => $reservation
        ]);
    }

    // Process mock payment
    public function processPayment(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($reservation->customer_id !== $user->id) {
            abort(403);
        }
        // Validate mock payment fields
        $request->validate([
            'card_number' => ['required', 'digits_between:13,19'],
            'expiry' => ['required', 'regex:/^(0[1-9]|1[0-2])\/(\d{2})$/'],
            'cvv' => ['required', 'digits_between:3,4'],
            'card_name' => ['required', 'string'],
        ]);
        // Simulate payment success (always succeed for mock)
        $reservation->status = 'active';
        $reservation->save();
        // Send email notifications
        $reservation->customer->notify(new \App\Notifications\ReservationConfirmed($reservation));
        $renter = $reservation->car->agency->renter;
        if ($renter) {
            $renter->notify(new \App\Notifications\NewBookingReceived($reservation));
        }
        // Redirect to confirmation page (for web), or return reservation (for API)
        if ($request->wantsJson()) {
            return response()->json(['reservation' => $reservation, 'message' => 'Payment successful!']);
        }
        return redirect()->route('reservations.confirmation', $reservation->id);
    }

    // Show confirmation page
    public function confirmation(Request $request, $id)
    {
        $reservation = Reservation::with(['car.agency', 'car.images'])->findOrFail($id);
        $user = Auth::user();
        
        if ($reservation->customer_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }
        
        if ($request->wantsJson()) {
            return response()->json(['reservation' => $reservation, 'message' => 'Reservation confirmed!']);
        }
        
        return Inertia::render('Reservations/Confirmation', [
            'reservation' => $reservation
        ]);
    }

    // Update reservation status (for renters to manage their bookings)
    public function updateStatus(Request $request, $id)
    {
        $reservation = Reservation::with('car.agency')->findOrFail($id);
        $user = Auth::user();
        
        // Authorization: only renter who owns the car or admin can update status
        if ($user->user_type === 'renter') {
            if ($reservation->car->agency->renter_id !== $user->id) {
                abort(403, 'You can only update status for your own car reservations.');
            }
        } elseif ($user->user_type !== 'admin') {
            abort(403, 'Only renters and admins can update reservation status.');
        }
        
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled'
        ]);
        
        $oldStatus = $reservation->status;
        $newStatus = $request->status;
        
        // Business logic for status transitions
        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'completed' => [], // Cannot change from completed
            'cancelled' => [], // Cannot change from cancelled
        ];
        
        if (!in_array($newStatus, $allowedTransitions[$oldStatus])) {
            return response()->json([
                'message' => "Cannot change status from {$oldStatus} to {$newStatus}"
            ], 422);
        }
        
        $reservation->status = $newStatus;
        $reservation->save();
        
        // Send notifications for status changes
        if ($newStatus === 'confirmed') {
            $reservation->customer->notify(new \App\Notifications\ReservationConfirmed($reservation));
        } elseif ($newStatus === 'cancelled') {
            $reservation->customer->notify(new \App\Notifications\ReservationCancelled($reservation));
        }
        
        return response()->json([
            'message' => 'Reservation status updated successfully!',
            'reservation' => $reservation->fresh(['car.agency', 'customer'])
        ]);
    }
} 