<?php

namespace App\Http\Controllers;

use App\Models\SatisfactionSurvey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function show(string $token)
    {
        $survey = SatisfactionSurvey::where('token', $token)
            ->with('hotel', 'booking')
            ->firstOrFail();

        return view('survey.show', compact('survey'));
    }

    public function store(string $token, Request $request)
    {
        $survey = SatisfactionSurvey::where('token', $token)->firstOrFail();

        if ($survey->isResponded()) {
            return redirect()->route('survey.show', $token);
        }

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $survey->update($data + ['responded_at' => now()]);

        return redirect()->route('survey.show', $token)->with('success', 'Thank you for your feedback!');
    }
}
