<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Agency;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewAgencySubmitted;

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
}
