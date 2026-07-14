<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Hotel $hotel, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::MAINTENANCE_REQUESTS), 403,
            'Maintenance Requests is not enabled for this hotel.'
        );

        $query = MaintenanceRequest::forHotel($hotel->id)
            ->with(['room.roomType', 'reporter', 'assignee'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->paginate(30)->withQueryString();

        $summary = [
            'pending'     => MaintenanceRequest::forHotel($hotel->id)->pending()->count(),
            'in_progress' => MaintenanceRequest::forHotel($hotel->id)->inProgress()->count(),
            'resolved'    => MaintenanceRequest::forHotel($hotel->id)->resolved()->count(),
        ];

        return view('owner.maintenance.index', compact('hotel', 'requests', 'summary'));
    }
}
