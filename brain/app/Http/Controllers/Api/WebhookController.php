<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function receiveLead(Request $request)
    {
        // Parse full JSON payload
        $payload = $request->all();
        // Create the lead and persist individual fields
        $lead = \App\Models\Lead::create([
            // Contact info
            'first_name'        => $payload['contact']['first_name'] ?? null,
            'last_name'         => $payload['contact']['last_name']  ?? null,
            'phone'             => $payload['contact']['phone']      ?? null,
            'email'             => $payload['contact']['email']      ?? null,
            'address'           => $payload['contact']['address']    ?? null,
            'city'              => $payload['contact']['city']       ?? null,
            'state'             => $payload['contact']['state']      ?? null,
            'zip'               => $payload['contact']['zip_code']   ?? null,

            // First vehicle in array
            'vehicle_year'      => $payload['data']['vehicles'][0]['year']  ?? null,
            'vehicle_make'      => $payload['data']['vehicles'][0]['make']  ?? null,
            'vehicle_model'     => $payload['data']['vehicles'][0]['model'] ?? null,
            'vin'               => $payload['data']['vehicles'][0]['vin']   ?? null,

            // Policy info
            'insurance_company' => $payload['data']['current_policy']['company']
                                      ?? $payload['data']['requested_policy']['coverage_type']
                                      ?? null,
            'coverage_type'     => $payload['data']['requested_policy']['coverage_type'] ?? null,

            // Other lead columns
            'source'            => 'LQF',
            'type'              => 'internet',
            'received_at'       => now(),

            // Raw JSON store
            'payload'           => $payload,
        ]);

        return response()->json([
            'success' => true,
            'lead_id' => $lead->id,
        ], 201);
    }
}
