<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApprovedRenterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Check if user is a renter
        if (!$user || $user->user_type !== 'renter') {
            return redirect('/')->with('error', 'Access denied.');
        }
        
        // Check if renter has an approved agency
        $agency = $user->agency;
        if (!$agency || $agency->verification_status !== 'approved') {
            return redirect('/')->with('info', 'Your agency application is under review. You will be notified once approved.');
        }
        
        return $next($request);
    }
}