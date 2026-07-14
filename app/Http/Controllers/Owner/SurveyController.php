<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\SatisfactionSurvey;

class SurveyController extends Controller
{
    public function index(Hotel $hotel)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::GUEST_SURVEYS), 403,
            'Guest Satisfaction Surveys is not enabled for this hotel.'
        );

        $surveys = SatisfactionSurvey::forHotel($hotel->id)->with('user', 'booking')->latest()->paginate(20);

        $responded = SatisfactionSurvey::forHotel($hotel->id)->responded();

        $summary = [
            'sent'          => SatisfactionSurvey::forHotel($hotel->id)->count(),
            'responded'     => (clone $responded)->count(),
            'average_rating' => round((clone $responded)->avg('rating') ?? 0, 1),
        ];
        $summary['response_rate'] = $summary['sent'] > 0
            ? round(($summary['responded'] / $summary['sent']) * 100, 1)
            : 0;

        return view('owner.surveys.index', compact('hotel', 'surveys', 'summary'));
    }
}
