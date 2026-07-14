<?php

namespace App\Http\Controllers\Accountant;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Services\HotelReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private HotelReportService $reportService) {}

    public function index(Request $request)
    {
        /** @var Hotel $hotel */
        $hotel = $request->attributes->get('assigned_hotel');

        abort_unless($hotel->hasFeature(Feature::ADVANCED_ANALYTICS), 403,
            'Advanced Analytics is not enabled for this hotel.'
        );

        $period = (int) $request->get('period', 30);
        $data   = $this->reportService->buildReport($hotel, $period);

        return view('accountant.reports.index', $data);
    }
}
