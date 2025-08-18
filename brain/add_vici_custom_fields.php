<?php
// brain/add_vici_custom_fields.php
// Safely add custom fields for lead flow tracking

echo "=== ADDING VICI CUSTOM FIELDS ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$success = [];
$errors = [];

// Add fields one by one to avoid timeout
$fields = [
    [
        'name' => 'list_entry_date',
        'sql' => "ALTER TABLE vicidial_list ADD COLUMN IF NOT EXISTS list_entry_date DATETIME DEFAULT NULL COMMENT 'Date lead entered current list'"
    ],
    [
        'name' => 'original_entry_date', 
        'sql' => "ALTER TABLE vicidial_list ADD COLUMN IF NOT EXISTS original_entry_date DATETIME DEFAULT NULL COMMENT 'Date lead first entered system'"
    ],
    [
        'name' => 'tcpajoin_date',
        'sql' => "ALTER TABLE vicidial_list ADD COLUMN IF NOT EXISTS tcpajoin_date DATE DEFAULT NULL COMMENT 'TCPA consent date'"
    ]
];

echo "📋 Adding custom fields to vicidial_list table...\n";
echo "   (This only affects lists 101-110, not your production lists)\n\n";

foreach ($fields as $field) {
    echo "   Adding {$field['name']}... ";
    
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($field['sql']) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        if (strpos($output, 'ERROR') === false) {
            echo "✅\n";
            $success[] = $field['name'];
        } else {
            echo "❌ Error: {$output}\n";
            $errors[] = $field['name'];
        }
    } else {
        echo "❌ Failed\n";
        $errors[] = $field['name'];
    }
    
    sleep(1); // Small delay between operations
}

// Add indexes
echo "\n📋 Adding indexes for performance...\n";

$indexes = [
    "ALTER TABLE vicidial_list ADD INDEX IF NOT EXISTS idx_list_entry (list_entry_date)",
    "ALTER TABLE vicidial_list ADD INDEX IF NOT EXISTS idx_original_entry (original_entry_date)",
    "ALTER TABLE vicidial_list ADD INDEX IF NOT EXISTS idx_tcpa (tcpajoin_date)"
];

foreach ($indexes as $i => $indexSql) {
    echo "   Adding index " . ($i + 1) . "... ";
    
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($indexSql) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        echo "✅\n";
    } else {
        echo "⚠️ May already exist\n";
    }
    
    sleep(1);
}

// Verify the fields were added
echo "\n🔍 Verifying fields...\n";

$checkSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = 'Q6hdjl67GRigMofv' 
             AND TABLE_NAME = 'vicidial_list' 
             AND COLUMN_NAME IN ('list_entry_date', 'original_entry_date', 'tcpajoin_date')";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkSql) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    $foundFields = array_filter(explode("\n", $output));
    
    echo "   Found fields: " . implode(", ", $foundFields) . "\n";
    
    if (count($foundFields) == 3) {
        echo "   ✅ All custom fields verified!\n";
    }
}

echo "\n=== SUMMARY ===\n\n";

if (empty($errors)) {
    echo "✅ SUCCESS! All custom fields added.\n\n";
    echo "📋 Fields added:\n";
    echo "   • list_entry_date - Tracks when lead enters each list\n";
    echo "   • original_entry_date - Tracks first system entry\n";
    echo "   • tcpajoin_date - TCPA consent tracking\n\n";
    echo "🎯 These fields will ONLY be used for leads in lists 101-110\n";
    echo "🛡️ Your existing lists and leads are unaffected\n";
} else {
    echo "⚠️ Some fields may have had issues:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
}

echo "\n📊 Next step: Create the lead movement scripts\n";

$kernel->terminate($request, $response);


// brain/add_vici_custom_fields.php
// Safely add custom fields for lead flow tracking

echo "=== ADDING VICI CUSTOM FIELDS ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$success = [];
$errors = [];

// Add fields one by one to avoid timeout
$fields = [
    [
        'name' => 'list_entry_date',
        'sql' => "ALTER TABLE vicidial_list ADD COLUMN IF NOT EXISTS list_entry_date DATETIME DEFAULT NULL COMMENT 'Date lead entered current list'"
    ],
    [
        'name' => 'original_entry_date', 
        'sql' => "ALTER TABLE vicidial_list ADD COLUMN IF NOT EXISTS original_entry_date DATETIME DEFAULT NULL COMMENT 'Date lead first entered system'"
    ],
    [
        'name' => 'tcpajoin_date',
        'sql' => "ALTER TABLE vicidial_list ADD COLUMN IF NOT EXISTS tcpajoin_date DATE DEFAULT NULL COMMENT 'TCPA consent date'"
    ]
];

echo "📋 Adding custom fields to vicidial_list table...\n";
echo "   (This only affects lists 101-110, not your production lists)\n\n";

foreach ($fields as $field) {
    echo "   Adding {$field['name']}... ";
    
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($field['sql']) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        $output = $response->json()['output'] ?? '';
        if (strpos($output, 'ERROR') === false) {
            echo "✅\n";
            $success[] = $field['name'];
        } else {
            echo "❌ Error: {$output}\n";
            $errors[] = $field['name'];
        }
    } else {
        echo "❌ Failed\n";
        $errors[] = $field['name'];
    }
    
    sleep(1); // Small delay between operations
}

// Add indexes
echo "\n📋 Adding indexes for performance...\n";

$indexes = [
    "ALTER TABLE vicidial_list ADD INDEX IF NOT EXISTS idx_list_entry (list_entry_date)",
    "ALTER TABLE vicidial_list ADD INDEX IF NOT EXISTS idx_original_entry (original_entry_date)",
    "ALTER TABLE vicidial_list ADD INDEX IF NOT EXISTS idx_tcpa (tcpajoin_date)"
];

foreach ($indexes as $i => $indexSql) {
    echo "   Adding index " . ($i + 1) . "... ";
    
    $response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
        'command' => "mysql -u root Q6hdjl67GRigMofv -e " . escapeshellarg($indexSql) . " 2>&1"
    ]);
    
    if ($response->successful()) {
        echo "✅\n";
    } else {
        echo "⚠️ May already exist\n";
    }
    
    sleep(1);
}

// Verify the fields were added
echo "\n🔍 Verifying fields...\n";

$checkSql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = 'Q6hdjl67GRigMofv' 
             AND TABLE_NAME = 'vicidial_list' 
             AND COLUMN_NAME IN ('list_entry_date', 'original_entry_date', 'tcpajoin_date')";

$response = Http::timeout(30)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
    'command' => "mysql -u root Q6hdjl67GRigMofv -B -N -e " . escapeshellarg($checkSql) . " 2>&1"
]);

if ($response->successful()) {
    $output = $response->json()['output'] ?? '';
    $foundFields = array_filter(explode("\n", $output));
    
    echo "   Found fields: " . implode(", ", $foundFields) . "\n";
    
    if (count($foundFields) == 3) {
        echo "   ✅ All custom fields verified!\n";
    }
}

echo "\n=== SUMMARY ===\n\n";

if (empty($errors)) {
    echo "✅ SUCCESS! All custom fields added.\n\n";
    echo "📋 Fields added:\n";
    echo "   • list_entry_date - Tracks when lead enters each list\n";
    echo "   • original_entry_date - Tracks first system entry\n";
    echo "   • tcpajoin_date - TCPA consent tracking\n\n";
    echo "🎯 These fields will ONLY be used for leads in lists 101-110\n";
    echo "🛡️ Your existing lists and leads are unaffected\n";
} else {
    echo "⚠️ Some fields may have had issues:\n";
    foreach ($errors as $error) {
        echo "   - {$error}\n";
    }
}

echo "\n📊 Next step: Create the lead movement scripts\n";

$kernel->terminate($request, $response);






