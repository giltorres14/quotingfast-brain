<?php
/**
 * Script to import leads from LeadsQuotingFast CSV export
 * and map them to existing Vici leads
 * 
 * Usage: php import_lqf_leads.php leads.csv
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/bootstrap/app.php';

use App\Models\Lead;
use Illuminate\Support\Facades\DB;

if ($argc < 2) {
    echo "Usage: php import_lqf_leads.php <csv_file>\n";
    exit(1);
}

$csvFile = $argv[1];
if (!file_exists($csvFile)) {
    echo "Error: File not found: $csvFile\n";
    exit(1);
}

echo "Starting LQF lead import from: $csvFile\n";
echo "----------------------------------------\n";

// Open CSV file
$handle = fopen($csvFile, 'r');
if (!$handle) {
    echo "Error: Cannot open file\n";
    exit(1);
}

// Get headers
$headers = fgetcsv($handle);
echo "CSV Headers found: " . implode(', ', $headers) . "\n\n";

// Map CSV columns to Brain fields
$fieldMapping = [
    // Adjust these based on your actual CSV headers
    'phone' => 'phone',
    'first_name' => 'first_name',
    'last_name' => 'last_name',
    'email' => 'email',
    'address' => 'address',
    'city' => 'city',
    'state' => 'state',
    'zip' => 'zip_code',
    'zip_code' => 'zip_code',
    'lead_id' => 'lqf_lead_id', // Store original LQF ID
    'created_at' => 'created_at',
    'vici_lead_id' => 'vici_lead_id', // If you have this
];

$imported = 0;
$skipped = 0;
$errors = 0;

DB::beginTransaction();

try {
    while (($row = fgetcsv($handle)) !== false) {
        $data = array_combine($headers, $row);
        
        // Clean phone number
        $phone = preg_replace('/[^0-9]/', '', $data['phone'] ?? '');
        
        if (empty($phone)) {
            echo "Skipping row - no phone number\n";
            $skipped++;
            continue;
        }
        
        // Check if lead already exists by phone
        $existingLead = Lead::where('phone', $phone)->first();
        
        if ($existingLead) {
            echo "Lead already exists with phone: $phone (ID: {$existingLead->id})\n";
            
            // Update with any missing data
            $updated = false;
            
            // Update Vici lead ID if we have it and it's missing
            if (!empty($data['vici_lead_id']) && empty($existingLead->vici_lead_id)) {
                $existingLead->vici_lead_id = $data['vici_lead_id'];
                $updated = true;
            }
            
            // Update other missing fields
            foreach ($fieldMapping as $csvField => $dbField) {
                if (!empty($data[$csvField]) && empty($existingLead->$dbField)) {
                    $existingLead->$dbField = $data[$csvField];
                    $updated = true;
                }
            }
            
            if ($updated) {
                $existingLead->save();
                echo "  -> Updated missing fields\n";
            }
            
            $skipped++;
            continue;
        }
        
        // Create new lead
        $lead = new Lead();
        
        // Generate external_lead_id (13-digit timestamp format)
        $lead->external_lead_id = time() . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
        
        // Map basic fields
        $lead->phone = $phone;
        $lead->first_name = $data['first_name'] ?? '';
        $lead->last_name = $data['last_name'] ?? '';
        $lead->name = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
        $lead->email = $data['email'] ?? '';
        $lead->address = $data['address'] ?? '';
        $lead->city = $data['city'] ?? '';
        $lead->state = $data['state'] ?? '';
        $lead->zip_code = $data['zip'] ?? $data['zip_code'] ?? '';
        
        // Set source and type
        $lead->source = 'LQF_IMPORT';
        $lead->type = 'auto'; // Default to auto insurance
        
        // Store original LQF ID in meta
        $meta = [];
        if (!empty($data['lead_id'])) {
            $meta['lqf_lead_id'] = $data['lead_id'];
        }
        if (!empty($data['vici_lead_id'])) {
            $meta['vici_lead_id'] = $data['vici_lead_id'];
        }
        $lead->meta = json_encode($meta);
        
        // Parse and store drivers data if available
        if (!empty($data['drivers'])) {
            $lead->drivers = $data['drivers']; // Assuming JSON format
        }
        
        // Parse and store vehicles data if available
        if (!empty($data['vehicles'])) {
            $lead->vehicles = $data['vehicles']; // Assuming JSON format
        }
        
        // Set timestamps
        if (!empty($data['created_at'])) {
            $lead->created_at = date('Y-m-d H:i:s', strtotime($data['created_at']));
        }
        
        $lead->save();
        
        echo "Imported: {$lead->name} - Phone: {$lead->phone} - ID: {$lead->id}\n";
        
        // If we have a Vici lead ID, update Vici with the Brain's external_lead_id
        if (!empty($data['vici_lead_id'])) {
            updateViciLeadCode($data['vici_lead_id'], $lead->external_lead_id);
        }
        
        $imported++;
    }
    
    DB::commit();
    
    echo "\n========================================\n";
    echo "Import Complete!\n";
    echo "Imported: $imported\n";
    echo "Skipped: $skipped\n";
    echo "Errors: $errors\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    DB::rollback();
    echo "\nError during import: " . $e->getMessage() . "\n";
    echo "Rolling back all changes...\n";
    exit(1);
}

fclose($handle);

/**
 * Update Vici lead with Brain's external_lead_id
 */
function updateViciLeadCode($viciLeadId, $externalLeadId) {
    try {
        // Connect to Vici database
        $viciDb = new PDO(
            'mysql:host=' . env('VICI_DB_HOST', '167.172.253.47') . ';dbname=' . env('VICI_DB_NAME', 'asterisk'),
            env('VICI_DB_USER', 'cron'),
            env('VICI_DB_PASS', '1234')
        );
        
        // Update vendor_lead_code in vicidial_list
        $stmt = $viciDb->prepare("
            UPDATE vicidial_list 
            SET vendor_lead_code = :vendor_code
            WHERE lead_id = :lead_id
        ");
        
        $stmt->execute([
            'vendor_code' => $externalLeadId,
            'lead_id' => $viciLeadId
        ]);
        
        echo "  -> Updated Vici lead $viciLeadId with vendor_code: $externalLeadId\n";
        
    } catch (Exception $e) {
        echo "  -> Warning: Could not update Vici lead: " . $e->getMessage() . "\n";
    }
}
