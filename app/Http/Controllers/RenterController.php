<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Agency;

class RenterController extends Controller
{
    public function dashboard()
    {
        $user = request()->user();
        $agency = Agency::where('renter_id', $user->id)->first();
        return Inertia::render('Dashboard/RenterDashboard', [
            'user' => $user,
            'agency' => $agency,
        ]);
    }
}
