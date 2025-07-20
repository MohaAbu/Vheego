<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class CheckRegistrationEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowRegistrations = Setting::get('allow_registrations', true);
        
        if (!$allowRegistrations) {
            // If it's an AJAX request (like from Inertia), return JSON response
            if ($request->wantsJson() || $request->inertia()) {
                return response()->json([
                    'message' => 'Registration is currently disabled.'
                ], 403);
            }
            
            // For regular requests, redirect with error message
            return redirect('/login')->with('error', 'Registration is currently disabled by the administrator.');
        }

        return $next($request);
    }
}
