<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EmailTemplate;
use App\Jobs\SendOnboardingEmailJob;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function show(Client $client)
    {
        return view('onboarding.show', compact('client'));
    }

    public function sendEmail(Request $request, Client $client)
    {
        SendOnboardingEmailJob::dispatch($client);
        return redirect()->route('onboarding.show', $client)
                         ->with('status', 'Welcome email queued!');
    }
}
