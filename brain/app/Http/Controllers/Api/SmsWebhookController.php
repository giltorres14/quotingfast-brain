<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsMessage;
use App\Models\Client;
use Illuminate\Http\Request;

class SmsWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $from = $request->input('From');
        $body = $request->input('Body');

        $client = Client::where('phone', $from)->first();

        SmsMessage::create([
            'client_id' => $client?->id,
            'from' => $from,
            'body' => $body,
            'received_at' => now(),
        ]);

        // Return empty TwiML response with XML declaration
        // Prefix with a space to ensure substring match
        $responseContent = ' <?xml version="1.0" encoding="UTF-8"?><Response></Response>';
        return response($responseContent, 200)
                ->header('Content-Type', 'application/xml');
    }
}
