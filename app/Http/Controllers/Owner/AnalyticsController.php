<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\HotelReportService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(private HotelReportService $reportService) {}

    public function index(Hotel $hotel, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::ADVANCED_ANALYTICS), 403,
            'Advanced Analytics is not enabled for this hotel.'
        );

        $period = (int) $request->get('period', 30);
        $data   = $this->reportService->buildReport($hotel, $period);

        return view('owner.analytics.index', $data);
    }
}
