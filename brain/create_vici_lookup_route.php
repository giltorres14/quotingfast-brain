<?php
/**
 * Add this route to routes/web.php to handle Vici iframe lookups
 * by phone number when vendor_lead_code is missing
 */

// Add this route to your routes/web.php file:

Route::get('/agent/lead-by-phone/{phone}', function($phone) {
    // Clean the phone number
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    
    // Try to find lead by phone
    $lead = \App\Models\Lead::where('phone', $cleanPhone)
        ->orWhere('phone', 'LIKE', '%' . substr($cleanPhone, -10))
        ->orderBy('created_at', 'desc')
        ->first();
    
    if (!$lead) {
        // Create a placeholder lead from Vici data
        $lead = new \App\Models\Lead();
        $lead->phone = $cleanPhone;
        $lead->source = 'VICI_DIRECT';
        $lead->external_lead_id = time() . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        // Try to get data from Vici
        try {
            $viciDb = new PDO(
                'mysql:host=' . env('VICI_DB_HOST', '167.172.253.47') . ';dbname=' . env('VICI_DB_NAME', 'asterisk'),
                env('VICI_DB_USER', 'cron'),
                env('VICI_DB_PASS', '1234')
            );
            
            $stmt = $viciDb->prepare("
                SELECT * FROM vicidial_list 
                WHERE phone_number = :phone
                ORDER BY lead_id DESC
                LIMIT 1
            ");
            
            $stmt->execute(['phone' => $cleanPhone]);
            $viciLead = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($viciLead) {
                $lead->first_name = $viciLead['first_name'] ?? '';
                $lead->last_name = $viciLead['last_name'] ?? '';
                $lead->name = trim($lead->first_name . ' ' . $lead->last_name);
                $lead->address = $viciLead['address1'] ?? '';
                $lead->city = $viciLead['city'] ?? '';
                $lead->state = $viciLead['state'] ?? '';
                $lead->zip_code = $viciLead['postal_code'] ?? '';
                $lead->email = $viciLead['email'] ?? '';
                
                // Store Vici lead ID in meta
                $lead->meta = json_encode(['vici_lead_id' => $viciLead['lead_id']]);
                
                // Save the lead to Brain
                $lead->save();
                
                // Update Vici with the Brain's external_lead_id
                $updateStmt = $viciDb->prepare("
                    UPDATE vicidial_list 
                    SET vendor_lead_code = :vendor_code
                    WHERE lead_id = :lead_id
                ");
                
                $updateStmt->execute([
                    'vendor_code' => $lead->external_lead_id,
                    'lead_id' => $viciLead['lead_id']
                ]);
            }
        } catch (Exception $e) {
            \Log::error('Vici lookup failed: ' . $e->getMessage());
        }
        
        if (!$lead->exists) {
            // If still no lead, create a minimal one
            $lead->name = 'Unknown';
            $lead->save();
        }
    }
    
    // Redirect to the standard lead display page
    return redirect("/agent/lead/{$lead->external_lead_id}?iframe=1");
})->name('agent.lead-by-phone');
