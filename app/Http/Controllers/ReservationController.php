<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Car;
use App\Http\Requests\ReservationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    // List reservations for the current user (customer, renter, or admin)
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->user_type === 'customer') {
            // Customer: show their own reservations
            $reservations = Reservation::with('car.agency')
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
                ? Reservation::with('car.agency')
                    ->whereHas('car', function ($q) use ($agency) {
                        $q->where('agency_id', $agency->id);
                    })
                    ->orderByDesc('created_at')
                    ->get()
                : collect();
        } else {
            // Admin: show all reservations
            $reservations = Reservation::with('car.agency', 'customer')
                ->orderByDesc('created_at')
                ->get();
        }
        return response()->json($reservations);
    }

    // Show details for a single reservation
    public function show($id)
    {
        $reservation = Reservation::with('car.agency', 'customer')->findOrFail($id);
        // Authorization: only customer, renter (of car), or admin can view
        $user = Auth::user();
        if (
            $user->user_type === 'customer' && $reservation->customer_id !== $user->id ||
            $user->user_type === 'renter' && $reservation->car->agency->renter_id !== $user->id
        ) {
            abort(403);
        }
        return response()->json($reservation);
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
        return redirect()->route('reservations.payment', $reservation->id);
    }

    // Show mock payment form
    public function paymentForm($id)
    {
        $reservation = Reservation::with('car.agency')->findOrFail($id);
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($reservation->customer_id !== $user->id) {
            abort(403);
        }
        // For API: return reservation data; for web: return view (to be implemented)
        return response()->json(['reservation' => $reservation]);
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
    public function confirmation($id)
    {
        $reservation = Reservation::with('car.agency')->findOrFail($id);
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($reservation->customer_id !== $user->id) {
            abort(403);
        }
        // For API: return reservation data; for web: return view (to be implemented)
        return response()->json(['reservation' => $reservation, 'message' => 'Reservation confirmed!']);
    }
} 