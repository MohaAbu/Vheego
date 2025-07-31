<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->user_type === 'admin') {
            // Redirect to the real admin dashboard route
            return redirect()->route('dashboard.admin');
        } elseif ($user->user_type === 'renter') {
            $agency = $user->agency;
            
            // If renter doesn't have agency or is pending/rejected, redirect to homepage
            if (!$agency || $agency->verification_status !== 'approved') {
                return redirect('/')->with('info', 'Your agency application is under review. You will be notified once approved.');
            }
            
            // Redirect to the renter dashboard route
            return redirect()->route('dashboard.renter');
        } else {
            return redirect('/');
        }
    }
} 