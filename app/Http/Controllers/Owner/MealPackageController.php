<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\MealPackage;
use Illuminate\Http\Request;

class MealPackageController extends Controller
{
    public function index(Hotel $hotel)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::UPSELLING), 403,
            'The Upselling Engine is not enabled for this hotel.'
        );

        $mealPackages = MealPackage::forHotel($hotel->id)->latest()->get();

        return view('owner.meal-packages.index', compact('hotel', 'mealPackages'));
    }

    public function store(Hotel $hotel, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::UPSELLING), 403);

        $data = $this->validated($request);
        $data['hotel_id'] = $hotel->id;

        MealPackage::create($data);

        return back()->with('success', 'Meal package added.');
    }

    public function update(Hotel $hotel, MealPackage $mealPackage, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::UPSELLING), 403);
        abort_unless($mealPackage->hotel_id === $hotel->id, 403);

        $mealPackage->update($this->validated($request));

        return back()->with('success', 'Meal package updated.');
    }

    public function destroy(Hotel $hotel, MealPackage $mealPackage)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::UPSELLING), 403);
        abort_unless($mealPackage->hotel_id === $hotel->id, 403);

        $mealPackage->delete();

        return back()->with('success', "Meal package \"{$mealPackage->name}\" removed.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'         => ['required', 'string', 'max:150'],
            'description'  => ['nullable', 'string', 'max:500'],
            'price'        => ['required', 'numeric', 'min:0'],
            'pricing_type' => ['required', 'in:per_night,per_stay,per_guest'],
            'active'       => ['required', 'boolean'],
        ]);
    }
}
