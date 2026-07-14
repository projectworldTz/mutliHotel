<?php

namespace App\Http\Controllers\Owner;

use App\Enums\Feature;
use App\Http\Controllers\Controller;
use App\Mail\MarketingCampaignMail;
use App\Models\EmailCampaign;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CampaignController extends Controller
{
    public function index(Hotel $hotel)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::EMAIL_MARKETING), 403,
            'Email Marketing is not enabled for this hotel.'
        );

        $campaigns = EmailCampaign::forHotel($hotel->id)->latest()->paginate(20);

        return view('owner.campaigns.index', compact('hotel', 'campaigns'));
    }

    public function create(Hotel $hotel)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::EMAIL_MARKETING), 403);

        $audienceCounts = [
            EmailCampaign::AUDIENCE_PAST     => (new EmailCampaign(['hotel_id' => $hotel->id, 'audience' => EmailCampaign::AUDIENCE_PAST]))->targetUsers()->count(),
            EmailCampaign::AUDIENCE_UPCOMING => (new EmailCampaign(['hotel_id' => $hotel->id, 'audience' => EmailCampaign::AUDIENCE_UPCOMING]))->targetUsers()->count(),
            EmailCampaign::AUDIENCE_ALL      => (new EmailCampaign(['hotel_id' => $hotel->id, 'audience' => EmailCampaign::AUDIENCE_ALL]))->targetUsers()->count(),
        ];

        return view('owner.campaigns.create', compact('hotel', 'audienceCounts'));
    }

    public function store(Hotel $hotel, Request $request)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::EMAIL_MARKETING), 403);

        $data = $request->validate([
            'subject'  => ['required', 'string', 'max:150'],
            'body'     => ['required', 'string', 'max:5000'],
            'audience' => ['required', 'in:past_guests,upcoming_guests,all_guests'],
        ]);

        $campaign = EmailCampaign::create($data + [
            'hotel_id'   => $hotel->id,
            'created_by' => auth()->id(),
        ]);

        if ($request->boolean('send_now')) {
            $this->send($hotel, $campaign);
            return redirect()->route('owner.campaigns.index', $hotel)->with('success', "Campaign sent to {$campaign->recipient_count} guest(s).");
        }

        return redirect()->route('owner.campaigns.index', $hotel)->with('success', 'Campaign saved as draft.');
    }

    public function show(Hotel $hotel, EmailCampaign $campaign)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($campaign->hotel_id === $hotel->id, 403);

        return view('owner.campaigns.show', compact('hotel', 'campaign'));
    }

    public function send(Hotel $hotel, EmailCampaign $campaign)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($hotel->hasFeature(Feature::EMAIL_MARKETING), 403);
        abort_unless($campaign->hotel_id === $hotel->id, 403);

        if ($campaign->status === EmailCampaign::STATUS_SENT) {
            return back()->with('success', 'Campaign already sent.');
        }

        $recipients = $campaign->targetUsers();

        foreach ($recipients as $user) {
            Mail::to($user->email)->queue(new MarketingCampaignMail($campaign, $user));
        }

        $campaign->update([
            'status'          => EmailCampaign::STATUS_SENT,
            'recipient_count' => $recipients->count(),
            'sent_at'         => now(),
        ]);

        return back()->with('success', "Campaign sent to {$recipients->count()} guest(s).");
    }

    public function destroy(Hotel $hotel, EmailCampaign $campaign)
    {
        abort_unless($hotel->isOwnedBy(auth()->user()), 403);
        abort_unless($campaign->hotel_id === $hotel->id, 403);

        if ($campaign->status === EmailCampaign::STATUS_SENT) {
            return back()->withErrors(['campaign' => 'Sent campaigns cannot be deleted, only kept as a record.']);
        }

        $campaign->delete();

        return back()->with('success', 'Draft deleted.');
    }
}
