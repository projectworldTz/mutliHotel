<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReceptionistMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('receptionist')) {
            abort(403, 'Access denied.');
        }

        $hotel = $user->assignedHotel();

        if (! $hotel || $hotel->status !== 'active') {
            abort(403, 'You are not assigned to an active hotel.');
        }

        // Share the assigned hotel with all receptionist views/controllers
        $request->attributes->set('assigned_hotel', $hotel);
        view()->share('assignedHotel', $hotel);

        return $next($request);
    }
}
