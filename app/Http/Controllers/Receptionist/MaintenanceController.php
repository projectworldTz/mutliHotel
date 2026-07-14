<?php

namespace App\Http\Controllers\Receptionist;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');

        abort_unless($hotel->hasFeature(Feature::MAINTENANCE_REQUESTS), 403,
            'Maintenance Requests is not enabled for this hotel. Contact your hotel owner.'
        );

        $query = MaintenanceRequest::forHotel($hotel->id)
            ->with(['room.roomType', 'reporter', 'assignee', 'booking'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $requests = $query->paginate(30)->withQueryString();

        $summary = [
            'pending'     => MaintenanceRequest::forHotel($hotel->id)->pending()->count(),
            'in_progress' => MaintenanceRequest::forHotel($hotel->id)->inProgress()->count(),
            'resolved'    => MaintenanceRequest::forHotel($hotel->id)->resolved()->count(),
        ];

        $rooms = Room::whereHas('roomType', fn ($q) => $q->where('hotel_id', $hotel->id))
            ->orderBy('room_number')
            ->get(['id', 'room_number']);

        return view('receptionist.maintenance.index', compact('hotel', 'requests', 'summary', 'rooms'));
    }

    public function store(Request $request)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($hotel->hasFeature(Feature::MAINTENANCE_REQUESTS), 403);

        $data = $request->validate([
            'room_id'     => ['nullable', 'exists:rooms,id'],
            'category'    => ['required', 'in:plumbing,electrical,hvac,furniture,appliance,other'],
            'priority'    => ['required', 'in:normal,high,urgent'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        MaintenanceRequest::create($data + [
            'hotel_id'    => $hotel->id,
            'reported_by' => auth()->id(),
            'status'      => MaintenanceRequest::STATUS_PENDING,
        ]);

        return back()->with('success', 'Maintenance request logged.');
    }

    public function updateStatus(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($maintenanceRequest->hotel_id === $hotel->id, 403);

        $action = $request->input('action');

        match ($action) {
            'start'    => $maintenanceRequest->markInProgress(auth()->user()),
            'resolve'  => $maintenanceRequest->markResolved($request->input('resolution_notes')),
            default    => abort(422, 'Unknown action.'),
        };

        return back()->with('success', 'Request updated.');
    }

    public function destroy(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $hotel = $request->attributes->get('assigned_hotel');
        abort_unless($maintenanceRequest->hotel_id === $hotel->id, 403);
        abort_unless($maintenanceRequest->status === MaintenanceRequest::STATUS_PENDING, 403, 'Only pending requests can be deleted.');

        $maintenanceRequest->delete();

        return back()->with('success', 'Request removed.');
    }
}
