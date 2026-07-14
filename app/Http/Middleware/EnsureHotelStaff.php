<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHotelStaff
{
    private const STAFF_ROLES = ['receptionist', 'manager', 'cashier'];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $allowedRoles = $roles ?: self::STAFF_ROLES;

        if (! $user || ! $user->hasAnyRole($allowedRoles)) {
            abort(403, 'Access denied.');
        }

        $hotel = $user->assignedHotel();

        if (! $hotel || $hotel->status !== 'active') {
            abort(403, 'You are not assigned to an active hotel.');
        }

        // Share the assigned hotel with all staff views and controllers
        $request->attributes->set('assigned_hotel', $hotel);
        view()->share('assignedHotel', $hotel);

        return $next($request);
    }
}
